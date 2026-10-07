<?php

namespace App\Models;

use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    public const AUDIENCES = ['student', 'professional'];

    /** Highest score a single answer can give: 0 Never, 1 Occasionally, 2 Often or Always. */
    public const MAX_ANSWER = 2;

    /**
     * Mascot illustrations shipped with the app: what each shows, and words in a question that suggest it.
     *
     * @var array<string, array{label: string, keywords: list<string>}>
     */
    public const ILLUSTRATIONS = [
        'q1' => ['label' => 'Burning eyes', 'keywords' => ['burn', 'sting', 'hot']],
        'q2' => ['label' => 'Itchy eyes', 'keywords' => ['itch', 'rub', 'scratch']],
        'q3' => ['label' => 'Something in the eye', 'keywords' => ['something in', 'foreign', 'gritty', 'sand', 'dust']],
        'q4' => ['label' => 'Watery eyes', 'keywords' => ['water', 'tear', 'crying']],
        'q5' => ['label' => 'Blinking a lot', 'keywords' => ['blink', 'twitch']],
        'q6' => ['label' => 'Red eyes', 'keywords' => ['red', 'bloodshot', 'irritat']],
        'q7' => ['label' => 'Eye pain', 'keywords' => ['pain', 'sore', 'hurt', 'discomfort']],
        'q8' => ['label' => 'Heavy, tired eyelids', 'keywords' => ['eyelid', 'heavy', 'sleepy', 'drowsy', 'tired', 'fatigue', 'exhaust']],
        'q9' => ['label' => 'Dry eyes', 'keywords' => ['dry']],
        'q10' => ['label' => 'Blurry vision', 'keywords' => ['blur', 'fuzzy', 'unclear', 'hazy']],
        'q11' => ['label' => 'Double vision', 'keywords' => ['double', 'two images']],
        'q12' => ['label' => 'Trouble focusing', 'keywords' => ['focus', 'concentrat', 'nearby', 'reading']],
        'q13' => ['label' => 'Sensitive to light', 'keywords' => ['light', 'bright', 'sensitiv', 'sun']],
        'q14' => ['label' => 'Halos and glare', 'keywords' => ['halo', 'glare', 'reflection', 'rainbow', 'ring']],
        'q15' => ['label' => 'Vision getting worse', 'keywords' => ['worse', 'eyesight', 'deteriorat', 'glasses', 'weaker']],
        'q16' => ['label' => 'Headache', 'keywords' => ['headache', 'head', 'migraine', 'temple']],
    ];

    /**
     * The built-in illustration that best fits a question, or null when no keyword matches.
     * Words in the symptom name count more than words in the question.
     */
    public static function suggestIllustration(string $symptom, string $question): ?string
    {
        $symptom = Str::lower($symptom);
        $question = Str::lower($question);
        $best = null;
        $bestScore = 0;
        foreach (self::ILLUSTRATIONS as $key => $illustration) {
            $score = 0;
            foreach ($illustration['keywords'] as $keyword) {
                // Match at the start of a word, so "red" does not match "tired".
                $pattern = '/\b'.preg_quote($keyword, '/').'/u';
                $score += (preg_match($pattern, $symptom) ? 3 : 0) + (preg_match($pattern, $question) ? 1 : 0);
            }
            if ($score > $bestScore) {
                [$best, $bestScore] = [$key, $score];
            }
        }

        return $best;
    }

    protected $fillable = ['audience', 'position', 'symptom', 'question', 'image', 'is_active', 'translations'];

    protected $casts = [
        'position' => 'integer',
        'is_active' => 'boolean',
        'translations' => 'array',
    ];

    /**
     * Active questions for one audience, in display order.
     *
     * @param  Builder<Question>  $query
     */
    public function scopeForAudience(Builder $query, string $audience): void
    {
        $query->where('audience', $audience)->where('is_active', true)->orderBy('position')->orderBy('id');
    }

    /**
     * English question text mapped to its Filipino and Cebuano versions, merged into the app's dictionary.
     *
     * @return array{Filipino: array<string, string>, Cebuano: array<string, string>}
     */
    public function dictionary(): array
    {
        $dictionary = ['Filipino' => [], 'Cebuano' => []];
        foreach (Tip::LANGUAGES as $code => $language) {
            $translated = $this->translations[$code]['question'] ?? null;
            if (is_string($translated) && $translated !== '') {
                $dictionary[$language][$this->question] = $translated;
            }
        }

        return $dictionary;
    }

    /**
     * The illustration key for images shipped with the app ("q1"), or null.
     */
    public function bundledImage(): ?string
    {
        return $this->image && Str::startsWith($this->image, 'bundled:') ? Str::after($this->image, 'bundled:') : null;
    }

    /**
     * Public URL of an uploaded illustration, or null.
     */
    public function imageUrl(): ?string
    {
        return $this->image && ! $this->bundledImage() ? Storage::disk('public')->url($this->image) : null;
    }
}
