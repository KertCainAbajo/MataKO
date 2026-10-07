<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;
use App\Models\SecurityEvent;
use App\Services\TwoFactor;
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
    public function edit(Request $request, TwoFactor $twoFactor): View
    {
        $admin = $request->user();
        $setupSecret = $request->session()->has('two_factor_setup') ? decrypt($request->session()->get('two_factor_setup')) : null;
        $activity = AdminActivity::where('admin_id', $admin->id);

        return view('admin.profile', [
            'admin' => $admin,
            'actionCount' => (clone $activity)->count(),
            'signInCount' => (clone $activity)->where('action', 'login')->count(),
            'changeCount' => (clone $activity)->where('action', '!=', 'login')->count(),
            'passwordChangedAt' => (clone $activity)->where('action', 'user.password_reset')->where('description', 'like', 'Changed their own%')->latest('created_at')->value('created_at'),
            'recentActivity' => (clone $activity)->latest('created_at')->latest('id')->limit(6)->get(),
            'setupSecret' => $setupSecret,
            'setupQrCode' => $setupSecret ? $twoFactor->qrCodeSvg($admin, $setupSecret) : null,
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
            // Admins can see every user's data, so their passwords must be stronger than app users'.
            'password' => ['required', 'confirmed', 'different:current_password', Password::min(12)->mixedCase()->numbers()->symbols()],
        ], [
            'password.different' => 'The new password must be different from your current password.',
        ]);

        $admin->forceFill(['password' => Hash::make($data['password'])])->save();
        $admin->tokens()->delete();
        Auth::logoutOtherDevices($data['password']);
        $request->session()->regenerate();
        AdminActivity::record('user.password_reset', 'Changed their own admin password');
        SecurityEvent::record('password.changed', user: $admin, details: 'Admin changed their own password');

        return redirect()->route('admin.profile')->with('status', 'Password changed. Other browsers have been signed out.');
    }
}
