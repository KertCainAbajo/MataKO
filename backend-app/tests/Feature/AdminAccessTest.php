<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

    public function test_an_admin_can_change_their_name_and_email_with_their_password(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'old@example.com']);

        $this->actingAs($admin)->put(route('admin.profile.update'), ['name' => 'New Name', 'email' => 'wrong@example.com', 'current_password' => 'nope'])
            ->assertSessionHasErrorsIn('details', 'current_password');
        $this->assertSame('old@example.com', $admin->fresh()->email);

        $this->actingAs($admin)->put(route('admin.profile.update'), ['name' => 'New Name', 'email' => 'NEW@example.com', 'current_password' => 'password'])
            ->assertRedirect(route('admin.profile'));
        $this->assertSame(['New Name', 'new@example.com'], [$admin->fresh()->name, $admin->fresh()->email]);
    }

    public function test_an_admin_can_change_their_password(): void
    {
        $admin = User::factory()->admin()->create();

        // Admin passwords need 12+ characters with upper and lower case, a number and a symbol.
        foreach (['short', 'NewSecret123', 'newsecret123!x'] as $weak) {
            $this->actingAs($admin)->put(route('admin.profile.password'), ['current_password' => 'password', 'password' => $weak, 'password_confirmation' => $weak])
                ->assertSessionHasErrorsIn('password', 'password');
        }

        $this->actingAs($admin)->put(route('admin.profile.password'), ['current_password' => 'password', 'password' => 'New-Secret-2026', 'password_confirmation' => 'New-Secret-2026'])
            ->assertRedirect(route('admin.profile'));
        $this->assertTrue(Hash::check('New-Secret-2026', $admin->fresh()->password));
        $this->assertDatabaseHas('admin_activities', ['admin_id' => $admin->id, 'action' => 'user.password_reset']);
        $this->assertDatabaseHas('security_events', ['user_id' => $admin->id, 'type' => 'password.changed']);
    }

    public function test_the_profile_page_loads(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Profile Person']);

        $this->actingAs($admin)->get(route('admin.profile'))->assertOk()->assertSee('Profile Person')->assertSee('Password & security');
    }
}
