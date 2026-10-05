<?php

use App\Models\Question;
use App\Models\Tip;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Fill Filipino and Cebuano text that is still empty, using the app's built-in translations.
     * Translations an admin has already entered are kept.
     */
    public function up(): void
    {
        $known = json_decode(file_get_contents(database_path('data/initial-translations.json')), true);
        $codes = array_keys(Tip::LANGUAGES);

        Tip::query()->each(function (Tip $tip) use ($known, $codes): void {
            $translations = $tip->translations ?? [];
            foreach ($tip->translatableFields() as $field) {
                $english = $tip->field($field);
                foreach ($codes as $index => $code) {
                    if (is_string($english) && isset($known[$english]) && blank($translations[$code][$field] ?? null)) {
                        $translations[$code][$field] = $known[$english][$index];
                    }
                }
            }
            if ($translations !== ($tip->translations ?? [])) {
                $tip->forceFill(['translations' => $translations])->saveQuietly();
            }
        });

        Question::query()->each(function (Question $question) use ($known, $codes): void {
            $translations = $question->translations ?? [];
            foreach ($codes as $index => $code) {
                if (isset($known[$question->question]) && blank($translations[$code]['question'] ?? null)) {
                    $translations[$code]['question'] = $known[$question->question][$index];
                }
            }
            if ($translations !== ($question->translations ?? [])) {
                $question->forceFill(['translations' => $translations])->saveQuietly();
            }
        });
    }

    public function down(): void
    {
        // Filled-in translations are kept; they are ordinary content admins can edit.
    }
};
