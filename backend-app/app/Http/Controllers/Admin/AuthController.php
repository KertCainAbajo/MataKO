<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;
use App\Models\SecurityEvent;
use App\Models\User;
use App\Services\LoginGuard;
use App\Services\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /** Minutes an admin has to enter their two-factor code after the password. */
    private const CHALLENGE_MINUTES = 5;

    public function create(): View
    {
        return view('admin.login');
    }

    public function store(Request $request, LoginGuard $guard): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email', '')))]);
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $guard->ensureNotLocked($credentials['email']);

        // Only active admins may sign in here; regular app users get the same message as a wrong password.
        $admin = User::where('email', $credentials['email'])->where('is_admin', true)->whereNull('disabled_at')->first();
        if (! $admin || ! Hash::check($credentials['password'], $admin->password)) {
            $guard->failed($credentials['email'], 'admin.login.failed');
            throw ValidationException::withMessages(['email' => 'These credentials do not match an admin account.']);
        }
        $guard->succeeded($credentials['email']);

        if ($admin->hasTwoFactor()) {
            // The password was right; the admin is signed in only after the code from their phone.
            $request->session()->regenerate();
            $request->session()->put('two_factor', [
                'user_id' => $admin->id,
                'remember' => $request->boolean('remember'),
                'expires_at' => now()->addMinutes(self::CHALLENGE_MINUTES)->timestamp,
            ]);

            return redirect()->route('admin.two-factor');
        }

        return $this->signIn($request, $admin, $request->boolean('remember'));
    }

    public function twoFactor(Request $request): View|RedirectResponse
    {
        return $this->pendingAdmin($request) ? view('admin.two-factor') : redirect()->route('admin.login');
    }

    public function verifyTwoFactor(Request $request, LoginGuard $guard, TwoFactor $twoFactor): RedirectResponse
    {
        $admin = $this->pendingAdmin($request);
        if (! $admin) {
            return redirect()->route('admin.login')->withErrors(['email' => 'Your sign-in took too long. Please enter your password again.']);
        }

        $data = $request->validate(['code' => ['required', 'string', 'max:20']]);
        $guard->ensureNotLocked($admin->email, 'code', 'two-factor');

        $usedRecoveryCode = false;
        $valid = $twoFactor->verify($admin, $admin->two_factor_secret, $data['code'])
            || ($usedRecoveryCode = $twoFactor->useRecoveryCode($admin, $data['code']));
        if (! $valid) {
            $guard->failed($admin->email, 'admin.two_factor.failed', 'two-factor');
            throw ValidationException::withMessages(['code' => 'That code is not right. Enter the 6-digit code from your authenticator app, or a recovery code.']);
        }
        $guard->succeeded($admin->email, 'two-factor');

        $remember = $request->session()->pull('two_factor')['remember'] ?? false;
        if ($usedRecoveryCode) {
            SecurityEvent::record('admin.two_factor.recovery_used', user: $admin, details: count($admin->two_factor_recovery_codes ?? []).' recovery codes left');
        }

        return $this->signIn($request, $admin, $remember);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function signIn(Request $request, User $admin, bool $remember): RedirectResponse
    {
        Auth::login($admin, $remember);
        $request->session()->regenerate();
        $request->session()->put('admin_last_activity', now()->timestamp);
        $admin->forceFill(['last_login_at' => now()])->save();
        AdminActivity::record('login', 'Signed in to the admin dashboard');
        SecurityEvent::record('admin.login', user: $admin, details: $admin->hasTwoFactor() ? 'With two-factor code' : 'Password only');

        return redirect()->intended(route('admin.dashboard'));
    }

    private function pendingAdmin(Request $request): ?User
    {
        $pending = $request->session()->get('two_factor');
        if (! $pending || $pending['expires_at'] < now()->timestamp) {
            $request->session()->forget('two_factor');

            return null;
        }

        $admin = User::where('is_admin', true)->whereNull('disabled_at')->find($pending['user_id']);

        return $admin?->hasTwoFactor() ? $admin : null;
    }
}
