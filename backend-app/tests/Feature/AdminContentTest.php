<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\Tip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_each_tab_lists_its_content(): void
    {
        $this->actingAs($this->admin)->get(route('admin.tips.index', ['tab' => 'student']))->assertOk()->assertSee('Study Habits')->assertDontSee('Work Habits');
        $this->actingAs($this->admin)->get(route('admin.tips.index', ['tab' => 'fact']))->assertOk()->assertSee('blink rate by up to 60%');
        $this->actingAs($this->admin)->get(route('admin.tips.index', ['tab' => 'topics']))->assertOk()->assertSee('Light Sensitivity')->assertSee('Window Switching');
        $this->actingAs($this->admin)->get(route('admin.tips.index', ['tab' => 'exercises']))->assertOk()->assertSee('Eye Palming')->assertSee('60 seconds');
        $this->actingAs($this->admin)->get(route('admin.tips.index', ['tab' => 'results']))->assertOk()->assertSee('Severe Eye Strain')->assertSee('Low-Light Rest');
    }

    public function test_built_in_content_starts_with_filipino_and_cebuano(): void
    {
        $tip = Tip::where('title', 'Stay Hydrated')->firstOrFail();

        $this->assertSame('Uminom ng Sapat na Tubig', $tip->translations['fil']['title']);
        $this->assertSame('Pag-inom og Igo nga Tubig', $tip->translations['ceb']['title']);
        $this->assertNotEmpty(Tip::where('kind', 'topic')->where('key', 'light')->firstOrFail()->translations['fil']['impact']);
    }

    public function test_every_built_in_text_has_filipino_and_cebuano(): void
    {
        $missing = [];
        foreach (Tip::all() as $tip) {
            foreach ($tip->translatableFields() as $field) {
                foreach (array_keys(Tip::LANGUAGES) as $code) {
                    if (filled($tip->field($field)) && blank($tip->translations[$code][$field] ?? null)) {
                        $missing[] = "{$tip->kind} #{$tip->id} {$field} ({$code}): {$tip->field($field)}";
                    }
                }
            }
        }
        foreach (Question::all() as $question) {
            foreach (array_keys(Tip::LANGUAGES) as $code) {
                if (blank($question->translations[$code]['question'] ?? null)) {
                    $missing[] = "question #{$question->id} ({$code})";
                }
            }
        }

        $this->assertSame([], $missing);
    }

    public function test_a_tip_is_saved_with_its_translations_and_the_audience_of_its_category(): void
    {
        $category = Tip::where('kind', 'category')->where('audience', 'professional')->where('title', 'Work Habits')->firstOrFail();

        $this->actingAs($this->admin)->post(route('admin.tips.store'), [
            'kind' => 'tip', 'parent_id' => $category->id,
            'title' => 'Use a Standing Desk', 'body' => 'Change position during the day.', 'icon' => 'body', 'color' => '#2E90FA', 'is_active' => '1',
            'translations' => ['fil' => ['title' => 'Gumamit ng Standing Desk', 'body' => ''], 'ceb' => ['title' => 'Gamita ang Standing Desk']],
        ])->assertRedirect(route('admin.tips.index', ['tab' => 'professional']));

        $tip = Tip::where('title', 'Use a Standing Desk')->firstOrFail();
        $this->assertSame(['professional', $category->id, 3], [$tip->audience, $tip->parent_id, $tip->position]);
        $this->assertSame(['fil' => ['title' => 'Gumamit ng Standing Desk'], 'ceb' => ['title' => 'Gamita ang Standing Desk']], $tip->translations);
    }

    public function test_an_exercise_can_be_edited_including_its_length(): void
    {
        $exercise = Tip::where('kind', 'exercise')->where('key', 'blinking')->firstOrFail();

        $this->actingAs($this->admin)->put(route('admin.tips.update', $exercise), [
            'title' => 'Eye Blinking', 'card_title' => 'Blink Break', 'body' => 'Blink slowly', 'icon' => 'eye', 'page_title' => 'Blink Break',
            'seconds' => '45', 'why_title' => 'Why?', 'why_text' => 'Because.', 'tip' => 'Tip.', 'is_active' => '1',
            'translations' => ['fil' => ['card_title' => 'Pahinga sa Pagkurap']],
        ])->assertSessionHasNoErrors();

        $exercise->refresh();
        $this->assertSame(45, $exercise->meta['seconds']);
        $this->assertSame('Blink Break', $exercise->meta['card_title']);
        $this->assertSame('blinking', $exercise->key);
        $this->assertSame('Pahinga sa Pagkurap', $exercise->translations['fil']['card_title']);
    }

    public function test_result_levels_cannot_be_added_or_deleted(): void
    {
        $result = Tip::where('kind', 'result')->where('key', 'HIGH')->firstOrFail();

        $this->actingAs($this->admin)->delete(route('admin.tips.destroy', $result))->assertForbidden();
        $this->actingAs($this->admin)->post(route('admin.tips.store'), ['kind' => 'result', 'title' => 'Extreme', 'body' => 'x'])->assertForbidden();
        $this->assertModelExists($result);
    }

    public function test_icons_and_colours_are_validated(): void
    {
        $this->actingAs($this->admin)->post(route('admin.tips.store'), [
            'kind' => 'tip', 'parent_id' => Tip::where('kind', 'category')->value('id'), 'title' => 'T', 'body' => 'B', 'icon' => '<script>', 'color' => 'red',
        ])->assertSessionHasErrors(['icon', 'color']);
    }

    public function test_deleting_a_topic_deletes_its_symptoms_and_tips(): void
    {
        $topic = Tip::where('kind', 'topic')->where('key', 'focus')->firstOrFail();

        $this->actingAs($this->admin)->delete(route('admin.tips.destroy', $topic));

        $this->assertDatabaseMissing('tips', ['parent_id' => $topic->id]);
    }
}
