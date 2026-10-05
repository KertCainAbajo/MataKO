<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guests_are_sent_to_the_admin_login(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_an_admin_can_sign_in_and_see_the_dashboard(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'admin@example.com']);

        $this->post(route('admin.login.store'), ['email' => 'ADMIN@example.com ', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Results by strain level');
        $this->assertNotNull($admin->fresh()->last_login_at);
    }

    public function test_app_users_cannot_sign_in_to_the_admin(): void
    {
        User::factory()->create(['email' => 'student@example.com']);

        $this->post(route('admin.login.store'), ['email' => 'student@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_disabled_admins_cannot_sign_in(): void
    {
        User::factory()->admin()->disabled()->create(['email' => 'admin@example.com']);

        $this->post(route('admin.login.store'), ['email' => 'admin@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_signed_in_user_who_is_not_an_admin_is_signed_out(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.users.index'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }
}
