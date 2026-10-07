<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * Time-based one-time codes (the 6-digit codes in Google Authenticator, Microsoft Authenticator, Authy…)
 * and single-use recovery codes for when the phone is lost.
 */
class TwoFactor
{
    public function __construct(private Google2FA $google2fa = new Google2FA) {}

    public function newSecret(): string
    {
        return $this->google2fa->generateSecretKey(32);
    }

    /**
     * QR code that authenticator apps scan to add the account, as inline SVG.
     */
    public function qrCodeSvg(User $user, string $secret): string
    {
        $url = $this->google2fa->getQRCodeUrl('MataKo Admin', $user->email, $secret);
        $svg = (new Writer(new ImageRenderer(new RendererStyle(192, 1), new SvgImageBackEnd)))->writeString($url);

        return trim(substr($svg, strpos($svg, '<svg')));
    }

    /**
     * Accepts the current code or the one just before or after it (phone clocks drift), and each code only once.
     */
    public function verify(User $user, string $secret, string $code): bool
    {
        $code = preg_replace('/\D/', '', $code);
        if (strlen($code) !== 6) {
            return false;
        }

        // Codes change every 30 seconds; the slot comes from the app clock (which tests can move).
        $slot = intdiv(now()->timestamp, 30);
        $matched = $this->google2fa->verifyKeyNewer($secret, $code, Cache::get($this->lastUsedKey($user)), 1, $slot);
        if ($matched === false) {
            return false;
        }
        // Remember the time slot used, so the same code cannot be replayed by someone watching.
        Cache::put($this->lastUsedKey($user), $matched === true ? $slot : $matched, now()->addMinutes(5));

        return true;
    }

    /**
     * Eight single-use codes. Shown once; only their hashes are stored.
     *
     * @return array{plain: list<string>, hashed: list<string>}
     */
    public function newRecoveryCodes(): array
    {
        $plain = array_map(fn (): string => Str::upper(Str::random(5).'-'.Str::random(5)), range(1, 8));

        return ['plain' => $plain, 'hashed' => array_map(fn (string $code): string => Hash::make($code), $plain)];
    }

    /**
     * Uses up a recovery code if it matches one that has not been used yet.
     */
    public function useRecoveryCode(User $user, string $code): bool
    {
        $code = Str::upper(trim($code));
        $codes = $user->two_factor_recovery_codes ?? [];
        foreach ($codes as $index => $hash) {
            if (Hash::check($code, $hash)) {
                unset($codes[$index]);
                $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

                return true;
            }
        }

        return false;
    }

    private function lastUsedKey(User $user): string
    {
        return 'two-factor-last-used:'.$user->id;
    }
}
