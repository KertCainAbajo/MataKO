<?php

namespace App\Models;

use Database\Factories\TipFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Editable app content: Tips sections and tips, Today's Eye Tip, facts, eye health topics,
 * exercises and result-screen care tips.
 */
class Tip extends Model
{
    /** @use HasFactory<TipFactory> */
    use HasFactory;

    /** @var array<string, string> Languages that can be translated, by code. English is the main text. */
    public const LANGUAGES = ['fil' => 'Filipino', 'ceb' => 'Cebuano'];

    /**
     * What each kind of content holds. Fields named title, body, icon and color are columns; the rest live in
     * "meta". Types: text, textarea, icon, color, number, checkbox. Text fields can be translated.
     *
     * @var array<string, array{label: string, tab: string, parent?: string, fixed?: bool, fields: array<string, array{0: string, 1: string}>}>
     */
    public const KINDS = [
        'category' => ['label' => 'Tips category', 'tab' => 'audience', 'fields' => [
            'title' => ['Title', 'text'], 'body' => ['Short description', 'textarea'], 'icon' => ['Icon', 'icon'],
        ]],
        'tip' => ['label' => 'Tip', 'tab' => 'audience', 'parent' => 'category', 'fields' => [
            'title' => ['Title', 'text'], 'body' => ['Text', 'textarea'], 'icon' => ['Icon', 'icon'], 'color' => ['Icon colour', 'color'],
        ]],
        'daily_tip' => ['label' => "Today's Eye Tip", 'tab' => 'daily_tip', 'fields' => [
            'body' => ['Text', 'textarea'],
        ]],
        'fact' => ['label' => 'Did You Know? fact', 'tab' => 'fact', 'fields' => [
            'body' => ['Text', 'textarea'], 'icon' => ['Icon', 'icon'],
        ]],
        'topic' => ['label' => 'Eye health topic', 'tab' => 'topics', 'fields' => [
            'title' => ['Card title', 'text'], 'body' => ['Card summary', 'textarea'], 'icon' => ['Icon', 'icon'],
            'hero_title' => ['Page heading', 'text'], 'subtitle' => ['Page subtitle', 'textarea'],
            'impact_title' => ['Highlight title', 'text'], 'impact' => ['Highlight text', 'textarea'],
            'symptoms_title' => ['Symptoms heading', 'text'],
            'prompt_title' => ['Pop-up title', 'text'], 'prompt_text' => ['Pop-up message', 'textarea'],
            'prompt_cancel' => ['Pop-up cancel button', 'text'], 'prompt_confirm' => ['Pop-up confirm button', 'text'],
        ]],
        'topic_symptom' => ['label' => 'Topic symptom', 'tab' => 'topics', 'parent' => 'topic', 'fields' => [
            'title' => ['Symptom', 'text'], 'body' => ['Description', 'text'], 'icon' => ['Icon', 'icon'],
            'extra' => ['Only show after "View More"', 'checkbox'],
        ]],
        'topic_tip' => ['label' => 'Topic relief tip', 'tab' => 'topics', 'parent' => 'topic', 'fields' => [
            'title' => ['Title', 'text'], 'body' => ['Text', 'textarea'],
        ]],
        'exercise' => ['label' => 'Exercise', 'tab' => 'exercises', 'fields' => [
            'title' => ['Name in the tracker', 'text'], 'card_title' => ['Card title', 'text'], 'body' => ['Card description', 'text'],
            'icon' => ['Icon', 'icon'], 'page_title' => ['Page title', 'text'], 'seconds' => ['Length in seconds', 'number'],
            'why_title' => ['"Why" heading', 'text'], 'why_text' => ['"Why" text', 'textarea'], 'tip' => ['Tip', 'textarea'],
        ]],
        'result' => ['label' => 'Result level', 'tab' => 'results', 'fixed' => true, 'fields' => [
            'title' => ['Result label', 'text'], 'body' => ['Message', 'textarea'],
            'tips_title' => ['Care Tips heading', 'text'], 'tips_intro' => ['Care Tips intro', 'textarea'], 'note' => ['Note at the bottom', 'textarea'],
        ]],
        'result_tip' => ['label' => 'Result care tip', 'tab' => 'results', 'parent' => 'result', 'fields' => [
            'title' => ['Title', 'text'], 'body' => ['Text', 'textarea'], 'icon' => ['Icon', 'icon'],
        ]],
    ];

    /** @var list<string> */
    public const COLUMN_FIELDS = ['title', 'body', 'icon', 'color'];

    public const AUDIENCES = ['all', 'student', 'professional'];

    protected $fillable = ['audience', 'kind', 'key', 'parent_id', 'icon', 'color', 'title', 'body', 'meta', 'translations', 'position', 'is_active'];

    protected $casts = [
        'position' => 'integer',
        'is_active' => 'boolean',
        'meta' => 'array',
        'translations' => 'array',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Tip::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Tip::class, 'parent_id')->orderBy('position')->orderBy('id');
    }

    /**
     * Active content shown to a user with this role ("all" is shown to everyone).
     *
     * @param  Builder<Tip>  $query
     */
    public function scopeVisibleTo(Builder $query, string $role): void
    {
        $query->where('is_active', true)->whereIn('audience', ['all', $role])->orderBy('position')->orderBy('id');
    }

    /**
     * The English value of a field, from its column or from meta.
     */
    public function field(string $name): mixed
    {
        return in_array($name, self::COLUMN_FIELDS, true) ? $this->{$name} : ($this->meta[$name] ?? null);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public function fields(): array
    {
        return self::KINDS[$this->kind]['fields'];
    }

    /**
     * Fields whose text can be translated.
     *
     * @return list<string>
     */
    public function translatableFields(): array
    {
        return array_keys(array_filter($this->fields(), fn (array $field): bool => in_array($field[1], ['text', 'textarea'], true)));
    }

    public function kindLabel(): string
    {
        return self::KINDS[$this->kind]['label'];
    }

    /**
     * English text mapped to its Filipino and Cebuano versions, merged into the app's dictionary.
     *
     * @return array{Filipino: array<string, string>, Cebuano: array<string, string>}
     */
    public function dictionary(): array
    {
        $dictionary = ['Filipino' => [], 'Cebuano' => []];
        foreach ($this->translatableFields() as $field) {
            $english = $this->field($field);
            foreach (self::LANGUAGES as $code => $language) {
                $translated = $this->translations[$code][$field] ?? null;
                if (is_string($english) && $english !== '' && is_string($translated) && $translated !== '') {
                    $dictionary[$language][$english] = $translated;
                }
            }
        }

        return $dictionary;
    }
}
