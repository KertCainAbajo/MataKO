<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $admin = $request->user();
        $activity = AdminActivity::where('admin_id', $admin->id);

        return view('admin.profile', [
            'admin' => $admin,
            'actionCount' => (clone $activity)->count(),
            'signInCount' => (clone $activity)->where('action', 'login')->count(),
            'changeCount' => (clone $activity)->where('action', '!=', 'login')->count(),
            'passwordChangedAt' => (clone $activity)->where('action', 'user.password_reset')->where('description', 'like', 'Changed their own%')->latest('created_at')->value('created_at'),
            'recentActivity' => (clone $activity)->latest('created_at')->latest('id')->limit(6)->get(),
        ]);
    }

    /**
     * Change the signed-in admin's name and email. Needs the current password.
     */
    public function update(Request $request): RedirectResponse
    {
        $admin = $request->user();
        $request->merge(['email' => Str::lower(trim((string) $request->input('email', '')))]);
        $data = $request->validateWithBag('details', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin->id)],
            'current_password' => ['required', 'current_password'],
        ]);

        $oldEmail = $admin->email;
        $admin->forceFill(['name' => $data['name'], 'email' => $data['email']])->save();
        AdminActivity::record('user.updated', $oldEmail === $admin->email ? 'Updated their own profile' : "Changed their admin email from {$oldEmail} to {$admin->email}");

        return redirect()->route('admin.profile')->with('status', 'Profile saved.');
    }

    /**
     * Change the signed-in admin's password. Signs out every other browser and the mobile app.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $admin = $request->user();
        $data = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:current_password', Password::min(8)->letters()->numbers()],
        ], [
            'password.different' => 'The new password must be different from your current password.',
        ]);

        $admin->forceFill(['password' => Hash::make($data['password'])])->save();
        $admin->tokens()->delete();
        Auth::logoutOtherDevices($data['password']);
        $request->session()->regenerate();
        AdminActivity::record('user.password_reset', 'Changed their own admin password');

        return redirect()->route('admin.profile')->with('status', 'Password changed. Other browsers have been signed out.');
    }
}
