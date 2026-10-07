<?php

namespace App\Services;

use App\Models\SecurityEvent;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Locks an account for 15 minutes after 5 wrong passwords, whatever network the attempts come from.
 * This works alongside the per-address limit on the sign-in routes, which a password-guessing tool
 * could get around by switching addresses.
 */
class LoginGuard
{
    public const MAX_ATTEMPTS = 5;

    public const LOCK_SECONDS = 15 * 60;

    /**
     * @throws ValidationException when the account is locked.
     */
    public function ensureNotLocked(string $email, string $field = 'email', string $scope = 'password'): void
    {
        $key = $this->key($email, $scope);
        if (! RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            return;
        }

        SecurityEvent::record('login.blocked', $email);
        $minutes = max(1, (int) ceil(RateLimiter::availableIn($key) / 60));

        throw ValidationException::withMessages([
            $field => [__('Too many failed sign-in attempts. Try again in :minutes minutes.', ['minutes' => $minutes])],
        ]);
    }

    public function failed(string $email, string $type = 'login.failed', string $scope = 'password'): void
    {
        $key = $this->key($email, $scope);
        RateLimiter::hit($key, self::LOCK_SECONDS);
        SecurityEvent::record($type, $email);

        if (RateLimiter::attempts($key) === self::MAX_ATTEMPTS) {
            SecurityEvent::record('account.locked', $email, details: 'Locked for 15 minutes after '.self::MAX_ATTEMPTS.' failed attempts');
        }
    }

    public function succeeded(string $email, string $scope = 'password'): void
    {
        RateLimiter::clear($this->key($email, $scope));
    }

    /**
     * Wrong passwords and wrong two-factor codes are counted separately ($scope).
     */
    private function key(string $email, string $scope): string
    {
        return "login-failures:{$scope}:".sha1(Str::lower(trim($email)));
    }
}
