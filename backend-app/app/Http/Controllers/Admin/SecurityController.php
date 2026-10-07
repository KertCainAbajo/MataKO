<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Sanctum\PersonalAccessToken;

class SecurityController extends Controller
{
    public function __invoke(Request $request): View
    {
        $type = array_key_exists((string) $request->query('type'), SecurityEvent::TYPES) ? $request->query('type') : null;
        $since = now()->subDay();
        $admins = User::where('is_admin', true)->whereNull('disabled_at')->get(['id', 'name', 'email', 'two_factor_secret', 'two_factor_confirmed_at']);

        return view('admin.security', [
            'events' => SecurityEvent::with('user:id,name')->when($type, fn ($query) => $query->where('type', $type))
                ->latest('created_at')->latest('id')->paginate(25)->withQueryString(),
            'type' => $type,
            'stats' => [
                'failed' => SecurityEvent::whereIn('type', ['login.failed', 'admin.login.failed', 'admin.two_factor.failed'])->where('created_at', '>=', $since)->count(),
                'locked' => SecurityEvent::where('type', 'account.locked')->where('created_at', '>=', $since)->count(),
                'adminsWithTwoFactor' => $admins->filter->hasTwoFactor()->count(),
                'admins' => $admins->count(),
                'tokens' => PersonalAccessToken::where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))->count(),
            ],
            'adminsWithoutTwoFactor' => $admins->reject->hasTwoFactor()->values(),
            'layers' => $this->layers($request, $admins),
        ]);
    }

    /**
     * The protection layers and whether each is fully on in this installation.
     *
     * @return list<array{0: string, 1: string, 2: bool, 3: string}>
     */
    private function layers(Request $request, $admins): array
    {
        $online = app()->isProduction();

        return [
            ['Browser shield', 'Security headers and a content policy on every page; icons and scripts served from this server only.', true, 'On'],
            ['Encrypted connection', 'HTTPS for the website and the app.', $request->isSecure(), $request->isSecure() ? 'On' : ($online ? 'Turn on HTTPS on the server' : 'Not needed on this computer; required online')],
            ['Sign-in protection', 'Accounts lock for 15 minutes after 5 wrong passwords; sign-up and sign-in are rate limited; strong passwords required.', true, 'On'],
            ['Admin two-factor sign-in', 'A code from an authenticator app after the password.', $admins->isNotEmpty() && $admins->every->hasTwoFactor(), $admins->filter->hasTwoFactor()->count().' of '.$admins->count().' admins'],
            ['Session safety', 'Admins are signed out after 30 minutes of inactivity; app sign-ins expire after 30 days.', true, 'On'],
            ['Data protection', 'Passwords hashed, two-factor secrets encrypted, uploaded pictures rebuilt to remove hidden content, app token stored encrypted on the phone.', true, 'On'],
            ['Error details hidden', 'Visitors never see code or settings when something goes wrong.', ! config('app.debug'), config('app.debug') ? ($online ? 'Set APP_DEBUG=false now' : 'Debug is on for development; turn off online') : 'On'],
            ['Monitoring', 'Failed sign-ins, lockouts and sign-in changes are recorded here and kept for 90 days.', true, 'On'],
        ];
    }
}
