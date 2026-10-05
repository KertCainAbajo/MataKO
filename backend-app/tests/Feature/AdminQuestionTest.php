<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminQuestionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_questions_are_listed_per_audience(): void
    {
        $this->actingAs($this->admin)->get(route('admin.questions.index', ['audience' => 'professional']))
            ->assertOk()->assertSee('after a full shift using your computer')->assertDontSee('lectures on screen');
    }

    public function test_a_question_with_an_image_can_be_added_at_the_end(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->post(route('admin.questions.store'), [
            'audience' => 'student',
            'symptom' => 'Neck strain',
            'question' => 'Does your neck ache after studying?',
            'image' => UploadedFile::fake()->image('neck.png', 300, 300),
            'is_active' => '1',
        ])->assertRedirect(route('admin.questions.index', ['audience' => 'student']));

        $question = Question::where('symptom', 'Neck strain')->firstOrFail();
        $this->assertSame(17, $question->position);
        Storage::disk('public')->assertExists($question->image);
        $this->assertDatabaseHas('admin_activities', ['action' => 'question.created']);
    }

    public function test_symptom_names_must_be_unique_within_a_questionnaire(): void
    {
        $this->actingAs($this->admin)->post(route('admin.questions.store'), [
            'audience' => 'student', 'symptom' => 'Dry eyes', 'question' => 'Again?',
        ])->assertSessionHasErrors('symptom');
    }

    public function test_a_question_can_be_edited_and_hidden(): void
    {
        $question = Question::where('audience', 'student')->where('symptom', 'Dry eyes')->firstOrFail();

        $this->actingAs($this->admin)->put(route('admin.questions.update', $question), [
            'symptom' => 'Dry eyes', 'question' => 'Updated wording?', 'is_active' => '0',
        ])->assertSessionHasNoErrors();

        $question->refresh();
        $this->assertSame('Updated wording?', $question->question);
        $this->assertFalse($question->is_active);
        $this->assertSame('bundled:q9', $question->image);
    }

    public function test_question_translations_are_saved(): void
    {
        $question = Question::where('audience', 'student')->where('symptom', 'Dry eyes')->firstOrFail();

        $this->actingAs($this->admin)->put(route('admin.questions.update', $question), [
            'symptom' => 'Dry eyes', 'question' => 'Are your eyes dry?', 'is_active' => '1',
            'translations' => ['fil' => ['question' => 'Tuyo ba ang mata mo?'], 'ceb' => ['question' => '  ']],
        ])->assertSessionHasNoErrors();

        $this->assertSame(['fil' => ['question' => 'Tuyo ba ang mata mo?']], $question->fresh()->translations);
    }

    public function test_moving_a_question_swaps_it_with_its_neighbour(): void
    {
        $second = Question::where('audience', 'student')->where('position', 2)->firstOrFail();

        $this->actingAs($this->admin)->patch(route('admin.questions.move', $second), ['direction' => 'up']);

        $this->assertSame(1, $second->fresh()->position);
        $this->assertSame(2, Question::where('audience', 'student')->where('symptom', 'Burning sensation')->value('position'));
    }

    public function test_deleting_a_question_removes_its_uploaded_image(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->image('q.png')->store('questions', 'public');
        $question = Question::factory()->create(['image' => $path]);

        $this->actingAs($this->admin)->delete(route('admin.questions.destroy', $question));

        $this->assertModelMissing($question);
        Storage::disk('public')->assertMissing($path);
    }
}
