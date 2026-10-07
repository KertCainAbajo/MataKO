<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;
use App\Models\SecurityEvent;
use App\Services\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Turning two-factor sign-in on and off from the admin's profile. Each step asks for the current
 * password, so someone at an unlocked computer cannot change it.
 */
class TwoFactorController extends Controller
{
    /**
     * Step 1: make a new secret and show its QR code. Nothing changes until the admin confirms a code.
     */
    public function start(Request $request, TwoFactor $twoFactor): RedirectResponse
    {
        $request->validateWithBag('twoFactor', ['current_password' => ['required', 'current_password']]);
        $request->session()->put('two_factor_setup', encrypt($twoFactor->newSecret()));

        return redirect()->to(route('admin.profile').'#two-factor');
    }

    /**
     * Step 2: the first code from the app proves it was set up correctly; then the recovery codes are shown once.
     */
    public function confirm(Request $request, TwoFactor $twoFactor): RedirectResponse
    {
        $admin = $request->user();
        $data = $request->validateWithBag('twoFactor', ['code' => ['required', 'string', 'max:10']]);
        $pending = $request->session()->get('two_factor_setup');
        if (! $pending) {
            return redirect()->to(route('admin.profile').'#two-factor');
        }

        $secret = decrypt($pending);
        if (! $twoFactor->verify($admin, $secret, $data['code'])) {
            throw ValidationException::withMessages(['code' => 'That code is not right. Check the time on your phone and try the newest code.'])
                ->errorBag('twoFactor')->redirectTo(route('admin.profile').'#two-factor');
        }

        $codes = $twoFactor->newRecoveryCodes();
        $admin->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $codes['hashed'],
            'two_factor_confirmed_at' => now(),
        ])->save();
        $request->session()->forget('two_factor_setup');
        AdminActivity::record('user.two_factor', 'Turned on two-factor sign-in');
        SecurityEvent::record('admin.two_factor.enabled', user: $admin);

        return redirect()->to(route('admin.profile').'#two-factor')
            ->with('status', 'Two-factor sign-in is on.')
            ->with('recovery_codes', $codes['plain']);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $admin = $request->user();
        $request->validateWithBag('twoFactor', ['current_password' => ['required', 'current_password']]);

        $admin->forceFill(['two_factor_secret' => null, 'two_factor_recovery_codes' => null, 'two_factor_confirmed_at' => null])->save();
        $request->session()->forget('two_factor_setup');
        AdminActivity::record('user.two_factor', 'Turned off two-factor sign-in');
        SecurityEvent::record('admin.two_factor.disabled', user: $admin);

        return redirect()->to(route('admin.profile').'#two-factor')->with('status', 'Two-factor sign-in is off.');
    }
}
