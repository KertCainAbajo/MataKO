<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_new_sign_ups_are_unread_until_marked_as_read(): void
    {
        User::factory()->create(['name' => 'Ana Student']);

        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertSee('Ana Student')->assertSee('1 unread')->assertSee('Mark all read');

        $this->travel(1)->minute();
        $this->actingAs($this->admin)->post(route('admin.notifications.read'))->assertRedirect();

        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertSee('Ana Student')->assertSee('You are all caught up')->assertDontSee('Mark all read');
    }

    public function test_clear_all_hides_everything_until_something_new_happens(): void
    {
        User::factory()->create(['name' => 'Ana Student']);
        $this->travel(1)->minute();

        $this->actingAs($this->admin)->post(route('admin.notifications.clear'))->assertSessionHas('status', 'Notifications cleared.');
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertSee('No notifications')->assertDontSee('Signed up as a');

        $this->travel(1)->minute();
        User::factory()->create(['name' => 'Ben Newcomer']);
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertSee('Ben Newcomer')->assertSee('1 unread');
    }

    public function test_each_admin_keeps_their_own_notification_state(): void
    {
        $other = User::factory()->admin()->create();
        User::factory()->create(['name' => 'Ana Student']);
        $this->travel(1)->minute();

        $this->actingAs($this->admin)->post(route('admin.notifications.clear'));

        $this->actingAs($other)->get(route('admin.dashboard'))->assertSee('Ana Student')->assertSee('1 unread');
    }

    public function test_app_users_cannot_use_the_notification_actions(): void
    {
        $appUser = User::factory()->create();

        $this->actingAs($appUser)->post(route('admin.notifications.clear'))->assertRedirect();
        $this->assertNull($appUser->fresh()->notifications_cleared_at);
    }
}
