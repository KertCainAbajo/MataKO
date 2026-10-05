<?php

namespace Tests\Feature;

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
}
