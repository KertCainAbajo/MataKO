<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MakeAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_new_admin_account(): void
    {
        $this->artisan('matako:make-admin', ['email' => 'Boss@Example.com', '--name' => 'Boss'])
            ->expectsQuestion('Password (at least 8 characters)', 'secret-pass')
            ->expectsQuestion('Confirm password', 'secret-pass')
            ->assertSuccessful();

        $admin = User::where('email', 'boss@example.com')->firstOrFail();
        $this->assertTrue($admin->is_admin);
        $this->assertTrue(Hash::check('secret-pass', $admin->password));
    }

    public function test_it_promotes_an_existing_account_without_changing_the_password(): void
    {
        $user = User::factory()->disabled()->create(['email' => 'kert@example.com']);

        $this->artisan('matako:make-admin', ['email' => 'kert@example.com'])->assertSuccessful();

        $user->refresh();
        $this->assertTrue($user->is_admin);
        $this->assertFalse($user->isDisabled());
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_mismatched_passwords_create_nothing(): void
    {
        $this->artisan('matako:make-admin', ['email' => 'boss@example.com', '--name' => 'Boss'])
            ->expectsQuestion('Password (at least 8 characters)', 'secret-pass')
            ->expectsQuestion('Confirm password', 'other-pass')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'boss@example.com']);
    }
}
