<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AssessmentQuestionsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_role_gets_its_own_questionnaire(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $student = $this->getJson('/api/questions')->assertOk()->assertJsonCount(16, 'questions');
        $this->assertSame('Burning sensation', $student->json('questions.0.symptom'));
        $this->assertSame('q1', $student->json('questions.0.image_key'));
        $this->assertSame('Worsening vision (Q16)', $student->json('questions.15.symptom'));

        Sanctum::actingAs(User::factory()->professional()->create());
        $this->assertSame('Headache', $this->getJson('/api/questions')->json('questions.15.symptom'));
    }

    public function test_hidden_questions_are_left_out_and_order_follows_position(): void
    {
        Question::where('audience', 'student')->where('symptom', 'Itchy eyes')->update(['is_active' => false]);
        Question::where('audience', 'student')->where('symptom', 'Eye pain')->update(['position' => 0]);
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/questions')->assertJsonCount(15, 'questions');

        $this->assertSame('Eye pain', $response->json('questions.0.symptom'));
        $this->assertNotContains('Itchy eyes', array_column($response->json('questions'), 'symptom'));
    }

    public function test_scoring_uses_the_active_questions(): void
    {
        Question::where('audience', 'student')->where('position', '>', 3)->update(['is_active' => false]);
        Sanctum::actingAs(User::factory()->create());
        $answers = ['Burning sensation' => 2, 'Itchy eyes' => 2, 'Foreign body sensation' => 1];

        $this->postJson('/api/assessment', ['answers' => $answers])
            ->assertCreated()
            ->assertJsonPath('assessment.total_score', 5)
            ->assertJsonPath('assessment.max_score', 6)
            ->assertJsonPath('assessment.risk_level', 'HIGH');
    }

    public function test_answers_for_an_outdated_questionnaire_are_rejected(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $answers = array_fill_keys(Question::forAudience('professional')->pluck('symptom')->all(), 1);

        $this->postJson('/api/assessment', ['answers' => $answers])->assertUnprocessable();
    }

    public function test_the_assessment_is_unavailable_without_questions(): void
    {
        Question::query()->update(['is_active' => false]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/assessment', ['answers' => ['Dry eyes' => 1]])->assertUnprocessable();
    }
}
