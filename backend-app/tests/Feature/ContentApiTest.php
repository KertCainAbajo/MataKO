<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\Tip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ContentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_get_student_tips_and_shared_content(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/content')->assertOk()->assertJsonCount(4, 'categories')->assertJsonCount(2, 'facts');

        $this->assertSame(['Daily Eye Care', 'Screen Use', 'Study Habits', 'Lifestyle & Wellness'], array_column($response->json('categories'), 'title'));
        $this->assertSame(['water', '#2E90FA', 'Stay Hydrated', 'Drink 8 glasses of water daily to keep your eyes naturally lubricated.'], $response->json('categories.0.items.0'));
        $this->assertStringContainsString('blink more often', $response->json('daily_tip'));
    }

    public function test_professionals_get_their_categories(): void
    {
        Sanctum::actingAs(User::factory()->professional()->create());

        $this->assertSame('Work Habits', $this->getJson('/api/content')->json('categories.2.title'));
    }

    public function test_topics_exercises_and_results_are_included(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/content')->assertJsonCount(3, 'topics')->assertJsonCount(5, 'exercises');

        $light = collect($response->json('topics'))->firstWhere('id', 'light');
        $this->assertSame('Light Sensitivity (Photophobia)', $light['heroTitle']);
        $this->assertCount(3, $light['symptoms']);
        $this->assertSame([['eye-off', 'Blurred Vision', 'Trouble focusing on digital screens']], $light['moreSymptoms']);
        $this->assertSame(['Sensitive to bright light or screens?', 'Discover common triggers and how to manage light sensitivity effectively.', 'Cancel', 'View Info'], $light['prompt']);
        $this->assertSame(['palming', 60], [$response->json('exercises.0.id'), $response->json('exercises.0.seconds')]);
        $this->assertSame('Take Action Now!', $response->json('results.HIGH.tipsTitle'));
        $this->assertCount(5, $response->json('results.HIGH.tips'));
    }

    public function test_translations_map_english_text_to_each_language(): void
    {
        Tip::where('title', 'Stay Hydrated')->firstOrFail()->update(['translations' => ['fil' => ['title' => 'Uminom ng Tubig'], 'ceb' => ['title' => 'Inom og Tubig']]]);
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/content');

        $this->assertSame('Uminom ng Tubig', $response->json('translations.Filipino')['Stay Hydrated']);
        $this->assertSame('Inom og Tubig', $response->json('translations.Cebuano')['Stay Hydrated']);
    }

    public function test_questions_come_with_translations(): void
    {
        Question::where('audience', 'student')->where('symptom', 'Dry eyes')->firstOrFail()
            ->update(['translations' => ['fil' => ['question' => 'Tuyo ba ang mata mo?']]]);
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/questions');

        $this->assertSame('Tuyo ba ang mata mo?', $response->json('translations.Filipino')['Do your eyes feel dry during long periods of online activities?']);
    }

    public function test_hidden_content_is_left_out(): void
    {
        Tip::where('title', 'Stay Hydrated')->update(['is_active' => false]);
        Tip::where('kind', 'fact')->update(['is_active' => false]);
        Tip::where('kind', 'exercise')->where('key', 'rolling')->update(['is_active' => false]);
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/content')->assertJsonCount(0, 'facts')->assertJsonCount(4, 'exercises');

        $this->assertSame('Wear Sunglasses', $response->json('categories.0.items.0.2'));
    }

    public function test_server_messages_follow_the_app_language(): void
    {
        User::factory()->create(['email' => 'ana@example.com']);

        $this->withHeader('Accept-Language', 'fil')->postJson('/api/login', ['email' => 'ana@example.com', 'password' => 'wrong'])
            ->assertJsonPath('errors.email.0', 'Mali ang email o password.');

        $this->withHeader('Accept-Language', 'ceb')->postJson('/api/register', [])
            ->assertJsonPath('errors.name.0', 'Kinahanglan ang ngalan.');
    }
}
