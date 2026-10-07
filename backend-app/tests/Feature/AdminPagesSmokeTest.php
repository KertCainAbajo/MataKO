<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\Tip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_admin_page_opens_with_real_data(): void
    {
        $this->withoutVite();
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['role' => 'professional']);
        $assessment = $user->assessments()->create(['total_score' => 20, 'max_score' => 32, 'risk_level' => 'MEDIUM']);
        $assessment->symptoms()->create(['symptom_name' => 'Dry eyes', 'value' => 2]);

        $pages = [
            route('admin.dashboard'),
            route('admin.search', ['q' => 'a']),
            route('admin.profile'),
            route('admin.users.index'),
            route('admin.users.index', ['status' => 'disabled']),
            route('admin.users.show', $user),
            route('admin.users.edit', $user),
            route('admin.questions.index', ['audience' => 'student']),
            route('admin.questions.index', ['audience' => 'professional']),
            route('admin.questions.create', ['audience' => 'student']),
            route('admin.questions.edit', Question::first()),
            route('admin.assessments.index'),
            route('admin.assessments.index', ['risk' => 'HIGH']),
            route('admin.assessments.show', $assessment),
            route('admin.assessments.export'),
            route('admin.activity'),
            route('admin.activity.export'),
        ];
        foreach (['student', 'professional', 'fact', 'topics', 'exercises', 'results'] as $tab) {
            $pages[] = route('admin.tips.index', ['tab' => $tab]);
        }
        foreach (array_keys(Tip::KINDS) as $kind) {
            if ($tip = Tip::where('kind', $kind)->first()) {
                $pages[] = route('admin.tips.edit', $tip);
                if (! (Tip::KINDS[$kind]['fixed'] ?? false)) {
                    $pages[] = route('admin.tips.create', ['kind' => $kind, 'parent' => $tip->parent_id]);
                }
            }
        }

        foreach ($pages as $page) {
            $this->actingAs($admin)->get($page)->assertOk();
        }
    }
}
