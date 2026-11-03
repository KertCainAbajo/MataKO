<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\LoginGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EnsureAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    private function withAdminSettings(?string $email, ?string $password, ?string $reset = null): void
    {
        foreach (['ADMIN_EMAIL' => $email, 'ADMIN_PASSWORD' => $password, 'ADMIN_RESET_PASSWORD' => $reset] as $key => $value) {
            $value === null ? putenv($key) : putenv("{$key}={$value}");
            $_ENV[$key] = $_SERVER[$key] = $value;
        }
    }

    protected function tearDown(): void
    {
        $this->withAdminSettings(null, null);
        parent::tearDown();
    }

    public function test_it_creates_the_admin_from_the_settings_once(): void
    {
        $this->withAdminSettings('Boss@Example.com', 'Strong-Pass-2026');

        $this->artisan('matako:ensure-admin')->assertSuccessful();
        $admin = User::where('email', 'boss@example.com')->sole();
        $this->assertTrue($admin->is_admin);
        $this->assertTrue(Hash::check('Strong-Pass-2026', $admin->password));

        // Running again (every restart) with a different password never changes the existing one.
        $this->withAdminSettings('boss@example.com', 'Other-Pass-2026!');
        $this->artisan('matako:ensure-admin')->assertSuccessful();
        $this->assertTrue(Hash::check('Strong-Pass-2026', $admin->fresh()->password));
        $this->assertSame(1, User::count());
    }

    public function test_weak_passwords_are_refused_and_never_printed(): void
    {
        $this->withAdminSettings('boss@example.com', 'password123');

        $exitCode = Artisan::call('matako:ensure-admin');
        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringNotContainsString('password123', $output);
        $this->assertSame(0, User::count());
    }

    public function test_the_reset_setting_recovers_a_forgotten_admin_password(): void
    {
        $user = User::factory()->create(['email' => 'boss@example.com', 'password' => Hash::make('Forgotten-Pass-1!')]);
        $user->createToken('app');
        $guard = app(LoginGuard::class);
        foreach (range(1, LoginGuard::MAX_ATTEMPTS) as $attempt) {
            $guard->failed('boss@example.com', 'admin.login.failed');
        }

        $this->withAdminSettings('boss@example.com', 'Brand-New-Pass-2026!', 'true');
        $this->artisan('matako:ensure-admin')->assertSuccessful();

        $user->refresh();
        $this->assertTrue($user->is_admin);
        $this->assertTrue(Hash::check('Brand-New-Pass-2026!', $user->password));
        $this->assertSame(0, $user->tokens()->count());
        $guard->ensureNotLocked('boss@example.com'); // the lockout is lifted, so this no longer throws
        $this->assertDatabaseHas('security_events', ['type' => 'admin.password_reset', 'email' => 'boss@example.com']);
    }

    public function test_a_weak_reset_password_is_refused_and_changes_nothing(): void
    {
        User::factory()->create(['email' => 'boss@example.com', 'password' => Hash::make('Forgotten-Pass-1!')]);

        $this->withAdminSettings('boss@example.com', 'password123', 'true');
        $this->assertSame(1, Artisan::call('matako:ensure-admin'));
        $this->assertTrue(Hash::check('Forgotten-Pass-1!', User::sole()->password));
    }

    public function test_nothing_happens_without_settings(): void
    {
        $this->withAdminSettings(null, null);

        $this->artisan('matako:ensure-admin')->assertSuccessful();
        $this->assertSame(0, User::count());
    }
}
