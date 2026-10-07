<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The student questionnaire asked about worsening vision twice. Its 16th illustration shows a headache,
 * matching the professional questionnaire, so the duplicate becomes a headache question.
 */
return new class extends Migration
{
    private const DUPLICATE = 'Do you feel like your vision is slowly getting worse due to screen use?';

    public function up(): void
    {
        // Only change the question if an admin has not already rewritten it.
        DB::table('questions')
            ->where('audience', 'student')
            ->where('symptom', 'Worsening vision (Q16)')
            ->where('question', self::DUPLICATE)
            ->whereNotExists(fn ($query) => $query->from('questions as other')->where('other.audience', 'student')->where('other.symptom', 'Headache'))
            ->update([
                'symptom' => 'Headache',
                'question' => 'Do you get headaches after studying or using your devices for school for a long time?',
                'translations' => json_encode([
                    'fil' => ['question' => 'Sumasakit ba ang ulo mo pagkatapos ng matagal na pag-aaral o paggamit ng iyong mga device para sa paaralan?'],
                    'ceb' => ['question' => 'Mosakit ba ang imong ulo human sa dugay nga pagtuon o paggamit sa imong mga device para sa eskwelahan?'],
                ]),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        $translations = json_decode((string) DB::table('questions')->where('audience', 'student')->where('symptom', 'Worsening vision')->value('translations'), true) ?: [];

        DB::table('questions')
            ->where('audience', 'student')
            ->where('symptom', 'Headache')
            ->where('question', 'Do you get headaches after studying or using your devices for school for a long time?')
            ->update([
                'symptom' => 'Worsening vision (Q16)',
                'question' => self::DUPLICATE,
                'translations' => json_encode($translations),
                'updated_at' => now(),
            ]);
    }
};
