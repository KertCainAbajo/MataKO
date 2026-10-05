<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('audience', 20);
            $table->unsignedSmallInteger('position');
            $table->string('symptom', 60);
            $table->text('question');
            // "bundled:q1" uses an illustration shipped with the app; anything else is a path on the public disk.
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['audience', 'symptom']);
            $table->index(['audience', 'is_active', 'position']);
        });

        $now = now();
        $rows = [];
        foreach ($this->initialQuestions() as $audience => $questions) {
            foreach ($questions as $index => [$symptom, $question]) {
                $rows[] = [
                    'audience' => $audience,
                    'position' => $index + 1,
                    'symptom' => $symptom,
                    'question' => $question,
                    'image' => 'bundled:q'.($index + 1),
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        DB::table('questions')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }

    /**
     * The questionnaires the app shipped with before questions became editable.
     *
     * @return array<string, list<array{0: string, 1: string}>>
     */
    private function initialQuestions(): array
    {
        return [
            'student' => [
                ['Burning sensation', 'Do your eyes feel a burning sensation during or after using your gadgets for school?'],
                ['Itchy eyes', 'Do you experience itchy eyes after hours of reading or watching lectures on screen?'],
                ['Foreign body sensation', "Do you feel like there's something in your eye while studying or scrolling on your phone?"],
                ['Watery eyes', 'Do your eyes water while attending online classes or reading e-books?'],
                ['Excessive blinking', 'Do you blink excessively when focusing on a digital screen?'],
                ['Eye redness', 'Do your eyes turn red after long hours of studying online?'],
                ['Eye pain', 'Do you feel pain or discomfort in your eyes after using digital devices for school?'],
                ['Heavy eyelids', 'Do your eyelids feel heavy during or after screen time for academic work?'],
                ['Dry eyes', 'Do your eyes feel dry during long periods of online activities?'],
                ['Blurred vision', 'Do you experience blurry vision after staring at your screen for too long?'],
                ['Double vision', 'Do you sometimes see double images when reading or watching videos online?'],
                ['Difficulty focusing', 'Do you find it hard to focus on nearby text after using your device for schoolwork?'],
                ['Light sensitivity', 'Do your eyes feel more sensitive to light when using your devices?'],
                ['Colored halos', 'Do you see colored halos or glares around images on your screen?'],
                ['Worsening vision', 'Do you feel like your vision is slowly getting worse due to screen use?'],
                ['Worsening vision (Q16)', 'Do you feel like your vision is slowly getting worse due to screen use?'],
            ],
            'professional' => [
                ['Burning sensation', 'Do your eyes feel a burning sensation during or after working long hours in front of screen?'],
                ['Itchy eyes', 'Do you experience itchy eyes after using a computer for extended periods at work?'],
                ['Foreign body sensation', "Do you feel like there's something in your eye while you're working on digital tasks?"],
                ['Watery eyes', 'Do your eyes water while working on documents, emails, or spreadsheets?'],
                ['Excessive blinking', 'Do you notice yourself blinking more than usual during intense screen time?'],
                ['Eye redness', 'Do your eyes turn red by the end of your workday?'],
                ['Eye pain', 'Do you feel eye pain or discomfort after a full shift using your computer?'],
                ['Heavy eyelids', 'Do your eyelids feel heavy while finishing tasks or joining online meetings?'],
                ['Dry eyes', 'Do your eyes feel dry after hours of working in front of a digital screen?'],
                ['Blurred vision', 'Do you experience blurry vision after prolonged work-related screen use?'],
                ['Double vision', 'Do you sometimes see double images when switching between tabs or windows on your computer?'],
                ['Difficulty focusing', 'Do you struggle to focus on nearby objects or papers after working on your screen?'],
                ['Light sensitivity', 'Do you feel more sensitive to overhead lights or screen brightness during work?'],
                ['Colored halos', 'Do you see colored halos or reflections around screen text or objects after working too long?'],
                ['Worsening vision', 'Do you feel like your vision has been getting worse from regular work screen use?'],
                ['Headache', 'Do you experience headaches at work after being on your computer for hours?'],
            ],
        ];
    }
};
