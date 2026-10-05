<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $role = $request->query('role');
        $status = $request->query('status');

        $users = User::query()
            ->withCount('assessments')
            ->withMax('tokens as last_active_at', 'last_used_at')
            ->when($search !== '', fn ($query) => $query->where(fn ($inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->when(in_array($role, ['student', 'professional'], true), fn ($query) => $query->where('role', $role))
            ->when($role === 'admin', fn ($query) => $query->where('is_admin', true))
            ->when($status === 'disabled', fn ($query) => $query->whereNotNull('disabled_at'))
            ->when($status === 'active', fn ($query) => $query->whereNull('disabled_at'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'role', 'status'));
    }

    public function show(User $user): View
    {
        $user->loadCount('assessments')->loadMax('tokens as last_active_at', 'last_used_at');

        return view('admin.users.show', [
            'user' => $user,
            'assessments' => $user->assessments()->latest('created_at')->latest('id')->paginate(10),
        ]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email', '')))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'age' => ['nullable', 'integer', 'min:18', 'max:120'],
            'role' => ['required', 'in:student,professional'],
            'is_admin' => ['boolean'],
        ]);
        $makeAdmin = $request->boolean('is_admin');

        if ($user->is($request->user()) && ! $makeAdmin) {
            return back()->withErrors(['is_admin' => 'You cannot remove your own admin access.'])->withInput();
        }

        unset($data['is_admin']);
        $user->fill($data);
        $user->is_admin = $makeAdmin;
        $user->save();
        AdminActivity::record('user.updated', "Updated user {$user->email}");

        return redirect()->route('admin.users.show', $user)->with('status', 'User updated.');
    }

    /**
     * Disable or re-enable an account. Disabling signs the user out of every device.
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['status' => 'You cannot disable your own account.']);
        }

        if ($user->isDisabled()) {
            $user->forceFill(['disabled_at' => null])->save();
            AdminActivity::record('user.enabled', "Enabled user {$user->email}");

            return back()->with('status', 'Account enabled.');
        }

        $user->forceFill(['disabled_at' => now()])->save();
        $user->tokens()->delete();
        AdminActivity::record('user.disabled', "Disabled user {$user->email}");

        return back()->with('status', 'Account disabled and signed out of the app.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['password' => ['required', 'confirmed', Password::min(8)]]);

        $user->forceFill(['password' => Hash::make($data['password'])])->save();
        $user->tokens()->delete();
        AdminActivity::record('user.password_reset', "Reset the password for {$user->email}");

        return back()->with('status', 'Password changed. The user has been signed out of the app.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['status' => 'You cannot delete your own account.']);
        }

        $email = $user->email;
        $user->tokens()->delete();
        $user->delete();
        AdminActivity::record('user.deleted', "Deleted user {$email} and their assessments");

        return redirect()->route('admin.users.index')->with('status', 'User deleted.');
    }
}
