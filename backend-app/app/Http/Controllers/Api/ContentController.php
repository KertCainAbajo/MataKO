<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ContentController extends Controller
{
    /**
     * Everything the app shows that admins can edit, for the signed-in user's role. Texts are English;
     * "translations" maps each English text to its Filipino and Cebuano version for the app's dictionary.
     */
    public function index(Request $request): JsonResponse
    {
        $role = $request->user()->role === 'professional' ? 'professional' : 'student';
        $active = fn ($query) => $query->where('is_active', true);
        $items = Tip::visibleTo($role)->whereNull('parent_id')->with(['children' => $active])->get();
        $ofKind = fn (string $kind): Collection => $items->where('kind', $kind)->values();

        $categories = $ofKind('category')->map(fn (Tip $category): array => [
            'id' => $category->id,
            'icon' => $category->icon,
            'title' => $category->title,
            'text' => $category->body,
            'items' => $category->children->map(fn (Tip $tip): array => [$tip->icon, $tip->color, $tip->title, $tip->body])->values(),
        ]);

        // Rotate Today's Eye Tip by day so every user sees the same tip on the same day.
        $dailyTips = $ofKind('daily_tip')->pluck('body');
        $dailyTip = $dailyTips->isEmpty() ? null : $dailyTips[now()->dayOfYear % $dailyTips->count()];

        $facts = $ofKind('fact')->map(fn (Tip $fact): array => [$fact->icon ?: 'bulb', $fact->body]);

        $topics = $ofKind('topic')->map(fn (Tip $topic): array => [
            'id' => $topic->key ?: "topic-{$topic->id}",
            'title' => $topic->title,
            'heroTitle' => $topic->field('hero_title') ?: $topic->title,
            'icon' => $topic->icon ?: 'eye',
            'summary' => $topic->body,
            'subtitle' => $topic->field('subtitle'),
            'impactTitle' => $topic->field('impact_title'),
            'impact' => $topic->field('impact'),
            'symptomsTitle' => $topic->field('symptoms_title'),
            'symptoms' => $topic->children->where('kind', 'topic_symptom')->reject(fn (Tip $item): bool => (bool) $item->field('extra'))->map(fn (Tip $item): array => [$item->icon ?: 'eye', $item->title, $item->body])->values(),
            'moreSymptoms' => $topic->children->where('kind', 'topic_symptom')->filter(fn (Tip $item): bool => (bool) $item->field('extra'))->map(fn (Tip $item): array => [$item->icon ?: 'eye', $item->title, $item->body])->values(),
            'tips' => $topic->children->where('kind', 'topic_tip')->map(fn (Tip $item): array => [$item->title, $item->body])->values(),
            'prompt' => [$topic->field('prompt_title'), $topic->field('prompt_text'), $topic->field('prompt_cancel') ?: 'Cancel', $topic->field('prompt_confirm') ?: 'Continue'],
        ]);

        $exercises = $ofKind('exercise')->map(fn (Tip $exercise): array => [
            'id' => $exercise->key ?: "exercise-{$exercise->id}",
            'name' => $exercise->title,
            'card' => $exercise->field('card_title') ?: $exercise->title,
            'icon' => $exercise->icon ?: 'eye',
            'description' => $exercise->body,
            'page' => $exercise->field('page_title') ?: $exercise->title,
            'seconds' => max(5, (int) ($exercise->field('seconds') ?: 30)),
            'why' => $exercise->field('why_title'),
            'whyText' => $exercise->field('why_text'),
            'tip' => $exercise->field('tip'),
        ]);

        $results = $ofKind('result')->mapWithKeys(fn (Tip $result): array => [$result->key => [
            'label' => $result->title,
            'message' => $result->body,
            'tipsTitle' => $result->field('tips_title'),
            'tipsIntro' => $result->field('tips_intro'),
            'note' => $result->field('note') ?: null,
            'tips' => $result->children->map(fn (Tip $tip): array => [$tip->icon ?: 'eye', $tip->title, $tip->body])->values(),
        ]]);

        $translations = $items->flatMap(fn (Tip $item): array => [$item, ...$item->children])
            ->reduce(fn (array $carry, Tip $item): array => array_replace_recursive($carry, $item->dictionary()), ['Filipino' => [], 'Cebuano' => []]);

        return response()->json([
            'categories' => $categories,
            'daily_tip' => $dailyTip,
            'facts' => $facts,
            'topics' => $topics,
            'exercises' => $exercises,
            'results' => $results,
            'translations' => $translations,
        ]);
    }
}
