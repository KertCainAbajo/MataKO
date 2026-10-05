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
