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

    public function test_the_mascot_that_fits_the_question_is_suggested(): void
    {
        $this->assertSame('q16', Question::suggestIllustration('Headache', 'Does your head hurt after class?'));
        $this->assertSame('q9', Question::suggestIllustration('Dry eyes', 'Do your eyes feel dry?'));
        $this->assertSame('q8', Question::suggestIllustration('Tired eyes', 'Do your eyes feel tired at night?'));
        $this->assertNull(Question::suggestIllustration('Posture', 'Do you sit up straight?'));
    }

    public function test_a_new_question_gets_a_matching_mascot_when_no_picture_is_chosen(): void
    {
        $this->actingAs($this->admin)->post(route('admin.questions.store'), [
            'audience' => 'professional', 'symptom' => 'Eye twitching', 'question' => 'Do your eyes twitch or blink a lot during meetings?', 'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertSame('bundled:q5', Question::where('symptom', 'Eye twitching')->value('image'));
    }

    public function test_the_admin_can_choose_a_mascot_or_no_picture(): void
    {
        $this->actingAs($this->admin)->post(route('admin.questions.store'), [
            'audience' => 'student', 'symptom' => 'Neck strain', 'question' => 'Does your neck ache?', 'illustration' => 'q7', 'is_active' => '1',
        ])->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post(route('admin.questions.store'), [
            'audience' => 'student', 'symptom' => 'Dry mouth', 'question' => 'Is your mouth dry?', 'illustration' => 'none', 'is_active' => '1',
        ])->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post(route('admin.questions.store'), [
            'audience' => 'student', 'symptom' => 'Other', 'question' => 'Other?', 'illustration' => '../secret',
        ])->assertSessionHasErrors('illustration');

        $this->assertSame('bundled:q7', Question::where('symptom', 'Neck strain')->value('image'));
        $this->assertNull(Question::where('symptom', 'Dry mouth')->value('image'));
    }

    public function test_switching_from_an_upload_to_a_mascot_deletes_the_upload(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->image('q.png')->store('questions', 'public');
        $question = Question::factory()->create(['audience' => 'student', 'image' => $path]);

        $this->actingAs($this->admin)->get(route('admin.questions.edit', $question))->assertOk()->assertSee('Your upload');
        $this->actingAs($this->admin)->put(route('admin.questions.update', $question), [
            'symptom' => $question->symptom, 'question' => $question->question, 'illustration' => 'upload', 'is_active' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertSame($path, $question->fresh()->image);

        $this->actingAs($this->admin)->put(route('admin.questions.update', $question), [
            'symptom' => $question->symptom, 'question' => $question->question, 'illustration' => 'q13', 'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertSame('bundled:q13', $question->fresh()->image);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_the_student_questionnaire_has_no_duplicate_questions(): void
    {
        $questions = Question::where('audience', 'student')->pluck('question');

        $this->assertSame($questions->count(), $questions->unique()->count());
        $this->assertSame('bundled:q16', Question::where('audience', 'student')->where('symptom', 'Headache')->value('image'));
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
