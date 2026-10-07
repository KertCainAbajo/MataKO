<?php

namespace Tests\Feature;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleSignInTest extends TestCase
{
    use RefreshDatabase;

    private const CLIENT_ID = 'web-client.apps.googleusercontent.com';

    private string $privateKey = '';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.google.client_ids' => [self::CLIENT_ID]]);

        // Stand in for Google: our own signing key, published the way Google publishes its keys.
        $key = $this->newKey();
        openssl_pkey_export($key, $this->privateKey, null, $this->opensslOptions());
        $rsa = openssl_pkey_get_details($key)['rsa'];
        $encode = fn (string $value): string => rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
        Http::fake(['www.googleapis.com/oauth2/v3/certs' => Http::response(['keys' => [
            ['kty' => 'RSA', 'alg' => 'RS256', 'use' => 'sig', 'kid' => 'test-key', 'n' => $encode($rsa['n']), 'e' => $encode($rsa['e'])],
        ]])]);
    }

    private function newKey(): \OpenSSLAsymmetricKey
    {
        return openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA, ...$this->opensslOptions()]);
    }

    /**
     * PHP on Windows needs to be pointed at the OpenSSL config file it ships with to create keys.
     *
     * @return array<string, string>
     */
    private function opensslOptions(): array
    {
        $config = dirname(PHP_BINARY).'/extras/ssl/openssl.cnf';

        return PHP_OS_FAMILY === 'Windows' && is_file($config) ? ['config' => $config] : [];
    }

    private function token(array $claims = [], ?string $privateKey = null): string
    {
        return JWT::encode([
            'iss' => 'https://accounts.google.com',
            'aud' => self::CLIENT_ID,
            'sub' => '1234567890',
            'email' => 'Ana.Cruz@gmail.com',
            'email_verified' => true,
            'name' => 'Ana Cruz',
            'iat' => time(),
            'exp' => time() + 3600,
            ...$claims,
        ], $privateKey ?? $this->privateKey, 'RS256', 'test-key');
    }

    public function test_a_new_person_is_asked_for_their_details_then_signed_up(): void
    {
        $token = $this->token();

        $this->postJson('/api/auth/google', ['id_token' => $token])
            ->assertOk()->assertExactJson(['needs_profile' => true, 'name' => 'Ana Cruz', 'email' => 'ana.cruz@gmail.com']);
        $this->assertDatabaseCount('users', 0);

        $this->postJson('/api/auth/google', ['id_token' => $token, 'age' => 21, 'role' => 'professional', 'phone' => '09171234567'])
            ->assertCreated()->assertJsonPath('user.email', 'ana.cruz@gmail.com')->assertJsonPath('user.role', 'professional')
            ->assertJsonMissingPath('user.google_id')->assertJsonStructure(['token']);

        $user = User::sole();
        $this->assertSame(['Ana Cruz', '1234567890', 21], [$user->name, $user->google_id, $user->age]);

        // Next time the same Google account signs straight in.
        $this->postJson('/api/auth/google', ['id_token' => $token])->assertOk()->assertJsonPath('user.id', $user->id);
    }

    public function test_an_existing_account_with_the_same_email_is_linked(): void
    {
        $user = User::factory()->create(['email' => 'ana.cruz@gmail.com']);

        $response = $this->postJson('/api/auth/google', ['id_token' => $this->token()])->assertOk()->assertJsonPath('user.id', $user->id);

        $this->assertSame('1234567890', $user->fresh()->google_id);
        $this->withToken($response->json('token'))->getJson('/api/user')->assertOk();
    }

    public function test_sign_up_details_follow_the_normal_rules(): void
    {
        $this->postJson('/api/auth/google', ['id_token' => $this->token(), 'age' => 15, 'role' => 'teacher'])
            ->assertUnprocessable()->assertJsonValidationErrors(['age', 'role', 'phone']);
    }

    public function test_tokens_that_are_not_valid_for_this_app_are_rejected(): void
    {
        openssl_pkey_export($this->newKey(), $otherPrivateKey, null, $this->opensslOptions());

        $invalid = [
            'another app' => $this->token(['aud' => 'someone-else.apps.googleusercontent.com']),
            'not from Google' => $this->token(['iss' => 'https://evil.example.com']),
            'expired' => $this->token(['iat' => time() - 7200, 'exp' => time() - 3600]),
            'unverified email' => $this->token(['email_verified' => false]),
            'forged signature' => $this->token([], $otherPrivateKey),
            'garbage' => 'not-a-token',
        ];

        foreach ($invalid as $case => $token) {
            $this->postJson('/api/auth/google', ['id_token' => $token])
                ->assertUnprocessable()->assertJsonPath('errors.id_token.0', 'Google sign-in could not be verified. Please try again.');
        }
        $this->assertDatabaseCount('users', 0);
    }

    public function test_disabled_accounts_cannot_sign_in_with_google(): void
    {
        User::factory()->create(['email' => 'ana.cruz@gmail.com', 'disabled_at' => now()]);

        $this->postJson('/api/auth/google', ['id_token' => $this->token()])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_it_explains_when_google_sign_in_is_not_set_up(): void
    {
        config(['services.google.client_ids' => []]);

        $this->withHeader('Accept-Language', 'ceb')->postJson('/api/auth/google', ['id_token' => $this->token()])
            ->assertUnprocessable()->assertJsonPath('errors.id_token.0', 'Wala pa ma-set up ang Google sign-in sa server.');
    }
}
