<?php

namespace Tests\Feature;

use App\Models\AdminActivity;
use App\Models\Assessment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminResultsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::factory()->admin()->create();
    }

    private function assessment(User $user, string $risk): Assessment
    {
        $assessment = Assessment::create(['user_id' => $user->id, 'total_score' => 4, 'max_score' => 32, 'risk_level' => $risk]);
        $assessment->symptoms()->create(['symptom_name' => 'Dry eyes', 'value' => 2]);

        return $assessment;
    }

    public function test_results_can_be_filtered_by_strain_level_and_user(): void
    {
        $this->assessment(User::factory()->create(['name' => 'Ana Mild']), 'LOW');
        $this->assessment(User::factory()->create(['name' => 'Ben Severe', 'email' => 'ben@example.com']), 'HIGH');
        // Move past the notification bell's 24-hour window so it does not list these users.
        $this->travel(2)->days();

        $this->actingAs($this->admin)->get(route('admin.assessments.index', ['risk' => 'HIGH']))->assertSee('Ben Severe')->assertDontSee('Ana Mild');
        $this->actingAs($this->admin)->get(route('admin.assessments.index', ['search' => 'ben@']))->assertSee('Ben Severe')->assertDontSee('Ana Mild');
    }

    public function test_a_result_shows_each_answer(): void
    {
        $assessment = $this->assessment(User::factory()->create(), 'LOW');

        $this->actingAs($this->admin)->get(route('admin.assessments.show', $assessment))->assertOk()->assertSee('Dry eyes')->assertSee('Often or Always');
    }

    public function test_results_can_be_exported_as_csv(): void
    {
        $this->assessment(User::factory()->create(['email' => 'ana@example.com']), 'LOW');

        $csv = $this->actingAs($this->admin)->get(route('admin.assessments.export'))->assertOk()->streamedContent();

        $this->assertStringContainsString('ana@example.com', $csv);
        $this->assertStringContainsString('Dry eyes: 2', $csv);
    }

    public function test_the_dashboard_and_activity_log_load(): void
    {
        $this->assessment(User::factory()->create(), 'MEDIUM');

        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Moderate');
        $this->actingAs($this->admin)->get(route('admin.activity'))->assertOk();
    }

    public function test_search_finds_users_questions_and_activity(): void
    {
        User::factory()->create(['name' => 'Ana Searchable']);

        $this->actingAs($this->admin)->get(route('admin.search', ['q' => 'searchable']))->assertOk()->assertSee('Ana Searchable');
        $this->actingAs($this->admin)->get(route('admin.search', ['q' => 'lectures on screen']))->assertOk()->assertSee('Itchy eyes');
    }

    public function test_the_bell_lists_todays_sign_ups(): void
    {
        User::factory()->create(['name' => 'Brand New User']);

        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Brand New User')->assertSee('Signed up as a student');
    }

    public function test_the_activity_log_can_be_filtered_by_type(): void
    {
        AdminActivity::create(['admin_id' => $this->admin->id, 'action' => 'question.created', 'description' => 'Added student question "Neck pain"']);
        AdminActivity::create(['admin_id' => $this->admin->id, 'action' => 'user.disabled', 'description' => 'Disabled user ana@example.com']);

        $this->actingAs($this->admin)->get(route('admin.activity', ['type' => 'questions']))
            ->assertOk()->assertSee('Neck pain')->assertDontSee('Disabled user ana@example.com');
        $this->actingAs($this->admin)->get(route('admin.activity', ['search' => 'ana@']))
            ->assertOk()->assertSee('Disabled user ana@example.com')->assertDontSee('Neck pain');
    }

    public function test_the_activity_log_filters_by_admin_and_exports(): void
    {
        $other = User::factory()->admin()->create(['name' => 'Second Admin']);
        AdminActivity::create(['admin_id' => $other->id, 'action' => 'content.updated', 'description' => 'Updated Tip "Stay Hydrated"']);
        AdminActivity::create(['admin_id' => $this->admin->id, 'action' => 'question.created', 'description' => 'Added student question "Neck pain"']);

        $this->actingAs($this->admin)->get(route('admin.activity', ['admin' => $other->id]))
            ->assertOk()->assertSee('Stay Hydrated')->assertDontSee('Neck pain');

        $csv = $this->actingAs($this->admin)->get(route('admin.activity.export', ['type' => 'questions']))->assertOk()->streamedContent();
        $this->assertStringContainsString('Neck pain', $csv);
        $this->assertStringNotContainsString('Stay Hydrated', $csv);
    }
}
