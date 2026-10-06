<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_users_can_be_searched_and_filtered(): void
    {
        User::factory()->create(['name' => 'Ana Student', 'email' => 'ana@example.com']);
        User::factory()->professional()->create(['name' => 'Ben Worker', 'email' => 'ben@example.com']);
        // Move past the notification bell's 24-hour window so it does not list these users.
        $this->travel(2)->days();

        $this->actingAs($this->admin)->get(route('admin.users.index', ['search' => 'ana']))
            ->assertOk()->assertSee('Ana Student')->assertDontSee('Ben Worker');

        $this->actingAs($this->admin)->get(route('admin.users.index', ['role' => 'professional']))
            ->assertOk()->assertSee('Ben Worker')->assertDontSee('Ana Student');
    }

    public function test_the_user_page_shows_their_assessments(): void
    {
        $user = User::factory()->create();
        Assessment::create(['user_id' => $user->id, 'total_score' => 20, 'max_score' => 32, 'risk_level' => 'MEDIUM']);

        $this->actingAs($this->admin)->get(route('admin.users.show', $user))
            ->assertOk()->assertSee('Moderate · 20/32');
    }

    public function test_disabling_a_user_signs_them_out_and_blocks_app_sign_in(): void
    {
        $user = User::factory()->create(['email' => 'ana@example.com']);
        $token = $user->createToken('mobile-app')->plainTextToken;

        $this->actingAs($this->admin)->patch(route('admin.users.status', $user))->assertRedirect();

        $this->assertTrue($user->fresh()->isDisabled());
        $this->assertSame(0, $user->tokens()->count());
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/user')->assertUnauthorized();
        $this->postJson('/api/login', ['email' => 'ana@example.com', 'password' => 'password'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_a_disabled_user_can_be_enabled_again(): void
    {
        $user = User::factory()->disabled()->create();

        $this->actingAs($this->admin)->patch(route('admin.users.status', $user));

        $this->assertFalse($user->fresh()->isDisabled());
    }

    public function test_admins_cannot_disable_delete_or_demote_themselves(): void
    {
        $this->actingAs($this->admin)->patch(route('admin.users.status', $this->admin))->assertSessionHasErrors('status');
        $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin))->assertSessionHasErrors('status');
        $this->actingAs($this->admin)->put(route('admin.users.update', $this->admin), [
            'name' => $this->admin->name, 'email' => $this->admin->email, 'role' => 'student', 'is_admin' => '0',
        ])->assertSessionHasErrors('is_admin');

        $this->assertTrue($this->admin->fresh()->is_admin);
        $this->assertFalse($this->admin->fresh()->isDisabled());
    }

    public function test_a_user_can_be_edited_and_made_admin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin)->put(route('admin.users.update', $user), [
            'name' => 'New Name', 'email' => 'NEW@example.com', 'phone' => '0917', 'age' => 30, 'role' => 'professional', 'is_admin' => '1',
        ])->assertRedirect(route('admin.users.show', $user));

        $user->refresh();
        $this->assertSame(['New Name', 'new@example.com', 'professional', true], [$user->name, $user->email, $user->role, $user->is_admin]);
        $this->assertDatabaseHas('admin_activities', ['admin_id' => $this->admin->id, 'action' => 'user.updated']);
    }

    public function test_resetting_a_password_replaces_it_and_signs_the_user_out(): void
    {
        $user = User::factory()->create();
        $user->createToken('mobile-app');

        $this->actingAs($this->admin)->put(route('admin.users.password', $user), [
            'password' => 'new-secret-1', 'password_confirmation' => 'new-secret-1',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-secret-1', $user->fresh()->password));
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_deleting_a_user_removes_their_assessments(): void
    {
        $user = User::factory()->create();
        Assessment::create(['user_id' => $user->id, 'total_score' => 3, 'max_score' => 32, 'risk_level' => 'LOW']);

        $this->actingAs($this->admin)->delete(route('admin.users.destroy', $user))->assertRedirect(route('admin.users.index'));

        $this->assertModelMissing($user);
        $this->assertDatabaseCount('assessments', 0);
    }
}
