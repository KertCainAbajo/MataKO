<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    // Layer 1: browser shield

    public function test_pages_send_security_headers_and_a_content_policy(): void
    {
        $response = $this->get(route('admin.login'))->assertOk()
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        $policy = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("frame-ancestors 'none'", $policy);
        $this->assertStringContainsString("object-src 'none'", $policy);
        $this->assertStringNotContainsString('unpkg.com', $policy);

        // Our inline script carries the nonce the policy allows.
        preg_match("/'nonce-([^']+)'/", $policy, $nonce);
        $this->assertStringContainsString('<script nonce="'.$nonce[1].'">', $response->getContent());

        $this->getJson('/api/user')->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_admin_pages_load_no_code_from_other_websites(): void
    {
        $admin = User::factory()->admin()->create();
        $html = $this->withoutVite()->actingAs($admin)->get(route('admin.dashboard'))->getContent();

        $this->assertStringNotContainsString('unpkg.com', $html);
        $this->assertMatchesRegularExpression('#<script type="module" src="[^"]+/ionicons/ionicons\.esm\.js" nonce="#', $html);
    }

    // Layer 2: sign-in protection

    public function test_an_app_account_locks_after_five_wrong_passwords(): void
    {
        User::factory()->create(['email' => 'ana@example.com']);

        foreach (range(1, 5) as $attempt) {
            // A different address each time: the per-address limit alone would not stop this.
            $this->withServerVariables(['REMOTE_ADDR' => "10.0.0.{$attempt}"])
                ->postJson('/api/login', ['email' => 'ana@example.com', 'password' => 'wrong-guess'])->assertUnprocessable();
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.99'])
            ->postJson('/api/login', ['email' => 'ana@example.com', 'password' => 'password'])
            ->assertUnprocessable()->assertJsonPath('errors.email.0', 'Too many failed sign-in attempts. Try again in 15 minutes.');
        $this->assertSame(5, SecurityEvent::where('type', 'login.failed')->count());
        $this->assertDatabaseHas('security_events', ['type' => 'account.locked', 'email' => 'ana@example.com']);
        $this->assertDatabaseHas('security_events', ['type' => 'login.blocked']);

        $this->travel(16)->minutes();
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.100'])
            ->postJson('/api/login', ['email' => 'ana@example.com', 'password' => 'password'])->assertOk();
    }

    public function test_the_lockout_message_is_translated(): void
    {
        User::factory()->create(['email' => 'ana@example.com']);
        foreach (range(1, 5) as $attempt) {
            $this->withServerVariables(['REMOTE_ADDR' => "10.0.1.{$attempt}"])->postJson('/api/login', ['email' => 'ana@example.com', 'password' => 'x']);
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.1.50'])->withHeader('Accept-Language', 'ceb')
            ->postJson('/api/login', ['email' => 'ana@example.com', 'password' => 'password'])
            ->assertJsonPath('errors.email.0', 'Daghan kaayo nga sayop nga pagsulod. Sulayi pag-usab human sa 15 ka minuto.');
    }

    public function test_sign_up_is_rate_limited_and_needs_a_stronger_password(): void
    {
        $form = fn (string $email, string $password = 'secret123') => [
            'name' => 'A', 'email' => $email, 'phone' => '1', 'age' => 20, 'role' => 'student',
            'password' => $password, 'password_confirmation' => $password,
        ];

        $this->postJson('/api/register', $form('a@example.com', 'onlyletters'))->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->postJson('/api/register', $form('b@example.com'))->assertCreated();
        $this->postJson('/api/register', $form('c@example.com'))->assertCreated();
        $this->postJson('/api/register', $form('d@example.com'))->assertStatus(429);
    }

    public function test_an_admin_account_locks_after_five_wrong_passwords(): void
    {
        User::factory()->admin()->create(['email' => 'boss@example.com']);

        foreach (range(1, 5) as $attempt) {
            $this->withServerVariables(['REMOTE_ADDR' => "10.0.2.{$attempt}"])
                ->post(route('admin.login.store'), ['email' => 'boss@example.com', 'password' => 'guess']);
        }
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.2.50'])
            ->post(route('admin.login.store'), ['email' => 'boss@example.com', 'password' => 'password'])->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame(5, SecurityEvent::where('type', 'admin.login.failed')->count());
    }

    // Layer 3: admin two-factor sign-in and sessions

    /** The code an authenticator app would show right now (by the app's clock, which tests move). */
    private function code(string $secret): string
    {
        return (new Google2FA)->oathTotp($secret, intdiv(now()->timestamp, 30));
    }

    private function enableTwoFactor(User $admin): string
    {
        $this->withoutVite()->actingAs($admin)->post(route('admin.two-factor.start'), ['current_password' => 'password'])->assertRedirect();
        $secret = decrypt(session('two_factor_setup'));
        $this->actingAs($admin)->post(route('admin.two-factor.confirm'), ['code' => $this->code($secret)])
            ->assertSessionHas('recovery_codes');

        return $secret;
    }

    public function test_an_admin_can_turn_on_two_factor_and_its_secret_is_stored_encrypted(): void
    {
        $admin = User::factory()->admin()->create();

        $this->withoutVite()->actingAs($admin)->post(route('admin.two-factor.start'), ['current_password' => 'wrong'])
            ->assertSessionHasErrorsIn('twoFactor', 'current_password');
        $this->actingAs($admin)->post(route('admin.two-factor.start'), ['current_password' => 'password']);
        $this->actingAs($admin)->get(route('admin.profile'))->assertOk()->assertSee('<svg', false)->assertSee('scan this QR code', false);
        $this->actingAs($admin)->post(route('admin.two-factor.confirm'), ['code' => '000000'])->assertSessionHasErrorsIn('twoFactor', 'code');
        $this->assertFalse($admin->fresh()->hasTwoFactor());

        $secret = decrypt(session('two_factor_setup'));
        $this->actingAs($admin)->post(route('admin.two-factor.confirm'), ['code' => $this->code($secret)]);

        $admin->refresh();
        $this->assertTrue($admin->hasTwoFactor());
        $this->assertCount(8, session('recovery_codes'));
        $this->assertSame($secret, $admin->two_factor_secret);
        $this->assertNotSame($secret, DB::table('users')->where('id', $admin->id)->value('two_factor_secret'));
        $this->assertDatabaseHas('security_events', ['type' => 'admin.two_factor.enabled', 'user_id' => $admin->id]);
    }

    public function test_sign_in_with_two_factor_needs_the_code(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'boss@example.com']);
        $secret = $this->enableTwoFactor($admin);
        auth()->logout();
        $this->flushSession();

        $this->post(route('admin.login.store'), ['email' => 'boss@example.com', 'password' => 'password'])->assertRedirect(route('admin.two-factor'));
        $this->assertGuest();
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));

        $this->post(route('admin.two-factor.verify'), ['code' => '123456'])->assertSessionHasErrors('code');
        $this->assertGuest();
        $this->assertDatabaseHas('security_events', ['type' => 'admin.two_factor.failed']);

        $this->travel(31)->seconds();
        $this->post(route('admin.two-factor.verify'), ['code' => $this->code($secret)])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_a_two_factor_code_cannot_be_used_twice(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'boss@example.com']);
        $secret = $this->enableTwoFactor($admin);
        $this->travel(31)->seconds();
        $code = $this->code($secret);

        foreach ([true, false] as $shouldWork) {
            auth()->logout();
            $this->flushSession();
            $this->post(route('admin.login.store'), ['email' => 'boss@example.com', 'password' => 'password']);
            $this->post(route('admin.two-factor.verify'), ['code' => $code]);
            $shouldWork ? $this->assertAuthenticatedAs($admin) : $this->assertGuest();
        }
    }

    public function test_a_recovery_code_works_once(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'boss@example.com']);
        $this->enableTwoFactor($admin);
        $recoveryCode = session('recovery_codes')[0];

        foreach ([true, false] as $shouldWork) {
            auth()->logout();
            $this->flushSession();
            $this->post(route('admin.login.store'), ['email' => 'boss@example.com', 'password' => 'password']);
            $this->post(route('admin.two-factor.verify'), ['code' => strtolower($recoveryCode)]);
            $shouldWork ? $this->assertAuthenticatedAs($admin) : $this->assertGuest();
        }
        $this->assertCount(7, $admin->fresh()->two_factor_recovery_codes);
        $this->assertDatabaseHas('security_events', ['type' => 'admin.two_factor.recovery_used']);
    }

    public function test_the_two_factor_step_expires(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'boss@example.com']);
        $secret = $this->enableTwoFactor($admin);
        auth()->logout();
        $this->flushSession();

        $this->post(route('admin.login.store'), ['email' => 'boss@example.com', 'password' => 'password']);
        $this->travel(6)->minutes();
        $this->post(route('admin.two-factor.verify'), ['code' => $this->code($secret)])->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_turning_two_factor_off_needs_the_password(): void
    {
        $admin = User::factory()->admin()->create();
        $this->enableTwoFactor($admin);

        $this->actingAs($admin)->delete(route('admin.two-factor.destroy'), ['current_password' => 'wrong'])->assertSessionHasErrorsIn('twoFactor', 'current_password');
        $this->assertTrue($admin->fresh()->hasTwoFactor());

        $this->actingAs($admin)->delete(route('admin.two-factor.destroy'), ['current_password' => 'password']);
        $this->assertFalse($admin->fresh()->hasTwoFactor());
        $this->assertDatabaseHas('security_events', ['type' => 'admin.two_factor.disabled']);
    }

    public function test_admins_are_signed_out_after_30_minutes_of_inactivity(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'boss@example.com']);
        $this->withoutVite()->post(route('admin.login.store'), ['email' => 'boss@example.com', 'password' => 'password']);
        $this->get(route('admin.dashboard'))->assertOk();

        $this->travel(29)->minutes();
        $this->get(route('admin.dashboard'))->assertOk();

        $this->travel(31)->minutes();
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'))->assertSessionHas('status');
        $this->assertGuest();
        $this->assertDatabaseHas('security_events', ['type' => 'admin.session.expired', 'user_id' => $admin->id]);
    }

    // Layer 4: tokens and data

    public function test_app_sign_ins_expire_after_30_days(): void
    {
        User::factory()->create(['email' => 'ana@example.com']);
        $token = $this->postJson('/api/login', ['email' => 'ana@example.com', 'password' => 'password'])->json('token');

        $this->withToken($token)->getJson('/api/user')->assertOk();
        $this->travel(31)->days();
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/user')->assertUnauthorized();
    }

    public function test_uploaded_pictures_are_rebuilt_without_hidden_content(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        // A real picture with extra data hidden after the image (how disguised scripts are smuggled in).
        $image = imagecreatetruecolor(1600, 800);
        ob_start();
        imagepng($image);
        $file = UploadedFile::fake()->createWithContent('trap.png', ob_get_clean().'HIDDEN-PAYLOAD-MARKER');

        $this->actingAs($admin)->post(route('admin.questions.store'), [
            'audience' => 'student', 'symptom' => 'Neck strain', 'question' => 'Does your neck ache?', 'image' => $file, 'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $stored = Storage::disk('public')->get(Question::where('symptom', 'Neck strain')->value('image'));
        $this->assertStringNotContainsString('HIDDEN-PAYLOAD-MARKER', $stored);
        $this->assertSame([1024, 512], array_slice(getimagesizefromstring($stored), 0, 2));
    }

    public function test_a_file_that_is_not_really_a_picture_is_refused(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $fake = UploadedFile::fake()->createWithContent('photo.png', 'plain text pretending to be a picture');

        $this->actingAs($admin)->post(route('admin.questions.store'), [
            'audience' => 'student', 'symptom' => 'Neck strain', 'question' => 'Does your neck ache?', 'image' => $fake, 'is_active' => '1',
        ])->assertSessionHasErrors('image');
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    // Layer 5: monitoring

    public function test_the_security_page_shows_events_to_admins_only(): void
    {
        $admin = User::factory()->admin()->create();
        SecurityEvent::create(['type' => 'admin.login.failed', 'email' => 'intruder@example.com', 'ip' => '203.0.113.9']);

        $this->withoutVite()->actingAs($admin)->get(route('admin.security'))->assertOk()
            ->assertSee('Failed admin sign-in')->assertSee('intruder@example.com')->assertSee('203.0.113.9')->assertSee('Protection layers');
        $this->actingAs(User::factory()->create())->get(route('admin.security'))->assertRedirect();
    }

    public function test_many_failed_sign_ins_raise_an_alert(): void
    {
        $admin = User::factory()->admin()->create();
        foreach (range(1, 10) as $attempt) {
            SecurityEvent::create(['type' => 'login.failed', 'email' => "user{$attempt}@example.com"]);
        }

        $this->withoutVite()->actingAs($admin)->get(route('admin.dashboard'))->assertSee('10 failed sign-ins in the last hour');
    }

    public function test_old_security_events_are_removed_after_90_days(): void
    {
        SecurityEvent::forceCreate(['type' => 'login.failed', 'email' => 'old@example.com', 'created_at' => now()->subDays(91)]);
        SecurityEvent::create(['type' => 'login.failed', 'email' => 'new@example.com']);

        $this->artisan('model:prune', ['--model' => [SecurityEvent::class]]);

        $this->assertSame(['new@example.com'], SecurityEvent::pluck('email')->all());
    }
}
