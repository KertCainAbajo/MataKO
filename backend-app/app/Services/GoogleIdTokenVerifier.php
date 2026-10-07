<?php

namespace App\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Checks a Google ID token sent by the app: Google's signature, issuer, expiry, and that it was issued to
 * one of our OAuth client IDs. Only then are the email and name in it trusted.
 */
class GoogleIdTokenVerifier
{
    private const CERTS_URL = 'https://www.googleapis.com/oauth2/v3/certs';

    private const ISSUERS = ['accounts.google.com', 'https://accounts.google.com'];

    /**
     * @return array{sub: string, email: string, name: string}
     *
     * @throws RuntimeException when the token is not a valid Google sign-in for this app.
     */
    public function verify(string $idToken): array
    {
        $clientIds = config('services.google.client_ids');
        if ($clientIds === []) {
            throw new RuntimeException('Google sign-in is not set up on the server yet.');
        }

        $keys = JWK::parseKeySet($this->googleKeys());
        try {
            // Allow a minute of difference between the phone's, Google's and this server's clocks.
            JWT::$leeway = 60;
            $claims = (array) JWT::decode($idToken, $keys);
        } catch (Throwable) {
            throw new RuntimeException('Google sign-in could not be verified. Please try again.');
        }

        if (! in_array($claims['iss'] ?? null, self::ISSUERS, true)
            || ! in_array($claims['aud'] ?? null, $clientIds, true)
            || empty($claims['sub'])
            || empty($claims['email'])
            || ($claims['email_verified'] ?? false) !== true) {
            throw new RuntimeException('Google sign-in could not be verified. Please try again.');
        }

        return [
            'sub' => (string) $claims['sub'],
            'email' => strtolower((string) $claims['email']),
            'name' => (string) ($claims['name'] ?? strstr((string) $claims['email'], '@', true)),
        ];
    }

    /**
     * Google's public signing keys, cached for an hour (Google rotates them every few days).
     *
     * @return array<string, mixed>
     */
    private function googleKeys(): array
    {
        return Cache::remember('google-id-token-keys', now()->addHour(), function (): array {
            $response = Http::timeout(10)->retry(1, 300, throw: false)->get(self::CERTS_URL);
            if (! $response->successful() || ! is_array($response->json('keys'))) {
                throw new RuntimeException('Google sign-in is not available right now. Try again in a moment.');
            }

            return $response->json();
        });
    }
}
