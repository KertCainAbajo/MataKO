<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Makes eye health topics, exercises and result-screen tips editable, and stores Filipino ("fil")
     * and Cebuano ("ceb") versions of every editable text: {"fil": {"title": "..."}, "ceb": {...}}.
     */
    public function up(): void
    {
        Schema::table('tips', function (Blueprint $table) {
            // Stable identifier the app uses for built-in layouts, e.g. exercise "palming" or result "HIGH".
            $table->string('key', 40)->nullable();
            $table->json('meta')->nullable();
            $table->json('translations')->nullable();
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->json('translations')->nullable();
        });

        $now = now();
        $insert = fn (array $row): int => DB::table('tips')->insertGetId($row + [
            'audience' => 'all', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);

        foreach ($this->topics() as $position => $topic) {
            $topicId = $insert(['kind' => 'topic', 'key' => $topic['key'], 'icon' => $topic['icon'], 'title' => $topic['title'], 'body' => $topic['summary'], 'position' => $position + 1, 'meta' => json_encode($topic['meta'])]);
            foreach ($topic['symptoms'] as $index => [$icon, $title, $body, $extra]) {
                $insert(['kind' => 'topic_symptom', 'parent_id' => $topicId, 'icon' => $icon, 'title' => $title, 'body' => $body, 'position' => $index + 1, 'meta' => json_encode(['extra' => $extra])]);
            }
            foreach ($topic['tips'] as $index => [$title, $body]) {
                $insert(['kind' => 'topic_tip', 'parent_id' => $topicId, 'icon' => 'time', 'title' => $title, 'body' => $body, 'position' => $index + 1]);
            }
        }

        foreach ($this->exercises() as $position => $exercise) {
            $insert(['kind' => 'exercise', 'key' => $exercise['key'], 'icon' => $exercise['icon'], 'title' => $exercise['name'], 'body' => $exercise['description'], 'position' => $position + 1, 'meta' => json_encode($exercise['meta'])]);
        }

        foreach ($this->results() as $position => $result) {
            $resultId = $insert(['kind' => 'result', 'key' => $result['key'], 'title' => $result['label'], 'body' => $result['message'], 'position' => $position + 1, 'meta' => json_encode($result['meta'])]);
            foreach ($result['tips'] as $index => [$icon, $title, $body]) {
                $insert(['kind' => 'result_tip', 'parent_id' => $resultId, 'icon' => $icon, 'title' => $title, 'body' => $body, 'position' => $index + 1]);
            }
        }

        // Fill in Filipino and Cebuano for every row from the translations the app already had.
        $known = json_decode(file_get_contents(database_path('data/initial-translations.json')), true);
        $translate = function (array $fields) use ($known): ?string {
            $result = [];
            foreach ($fields as $field => $english) {
                if (is_string($english) && $english !== '' && isset($known[$english])) {
                    $result['fil'][$field] = $known[$english][0];
                    $result['ceb'][$field] = $known[$english][1];
                }
            }

            return $result === [] ? null : json_encode($result, JSON_UNESCAPED_UNICODE);
        };

        foreach (DB::table('tips')->get() as $row) {
            $fields = ['title' => $row->title, 'body' => $row->body] + array_filter(json_decode($row->meta ?? '[]', true) ?: [], 'is_string');
            DB::table('tips')->where('id', $row->id)->update(['translations' => $translate($fields)]);
        }
        foreach (DB::table('questions')->get() as $row) {
            DB::table('questions')->where('id', $row->id)->update(['translations' => $translate(['question' => $row->question])]);
        }
    }

    public function down(): void
    {
        DB::table('tips')->whereIn('kind', ['topic', 'topic_symptom', 'topic_tip', 'exercise', 'result', 'result_tip'])->delete();
        Schema::table('tips', function (Blueprint $table) {
            $table->dropColumn(['key', 'meta', 'translations']);
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('translations');
        });
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function topics(): array
    {
        return [
            [
                'key' => 'movement', 'icon' => 'eye', 'title' => 'Eye Movement & Tracking',
                'summary' => 'Understand how your eyes follow movement and how to improve tracking.',
                'meta' => [
                    'hero_title' => 'Eye Movement & Tracking',
                    'subtitle' => 'Understanding Digital Eye Strain (DES) and its impact on visual tracking.',
                    'impact_title' => 'Digital Eye Strain (DES) Impact',
                    'impact' => 'Digital Eye Strain (DES) can impair smooth tracking and cause eye discomfort when reading long documents, scrolling, or switching windows.',
                    'symptoms_title' => 'Common Symptoms',
                    'prompt_title' => 'Want to improve how your eyes follow movement?',
                    'prompt_text' => "Let's explore daily habits and exercises to strengthen your eye tracking.",
                    'prompt_cancel' => 'Not Now',
                    'prompt_confirm' => 'Learn Now',
                ],
                'symptoms' => [
                    ['eye-off', 'Tracking Difficulties', 'Trouble following text across the screen', false],
                    ['reorder-four', 'Scrolling Discomfort', 'Eye strain during vertical movement', false],
                    ['albums', 'Window Switching', 'Fatigue when changing focus between screens', false],
                ],
                'tips' => [
                    ['30-30-30 Rule', 'Every 30 minutes, look at something 30 feet away for 30 seconds.'],
                    ['Eye Rolling', 'Roll your eyes slowly in circles to loosen the muscles that move them.'],
                    ['Increase Text Size', 'Larger text is easier to follow and reduces how far your eyes jump across a line.'],
                ],
            ],
            [
                'key' => 'focus', 'icon' => 'locate', 'title' => 'Accommodation & Focus Fatigue',
                'summary' => 'Why your eyes get tired when switching focus—and how to fix it.',
                'meta' => [
                    'hero_title' => 'Accommodation & Focus Fatigue',
                    'subtitle' => 'Difficulty shifting focus between near and far objects, often caused by prolonged screen use.',
                    'impact_title' => 'Focus Difficulty',
                    'impact' => 'Digital Eye Strain (DES) often leads to difficulty shifting focus between distances, a symptom of tired eye muscles.',
                    'symptoms_title' => 'Related Symptoms',
                    'prompt_title' => 'Having trouble refocusing your eyes during screen use?',
                    'prompt_text' => 'Learn why it happens and how to reduce focus fatigue.',
                    'prompt_cancel' => 'Later',
                    'prompt_confirm' => 'Continue',
                ],
                'symptoms' => [
                    ['eye', 'Blurred Vision', 'Difficulty seeing clearly at different distances', false],
                    ['sad', 'Eye Fatigue', 'Tired, heavy feelings in the eyes', false],
                    ['medical', 'Headaches', 'Tension headaches from eye strain', false],
                ],
                'tips' => [
                    ['30-30-30 Rule', 'Every 30 minutes, look at something 30 feet away for 30 seconds.'],
                    ['Focus Shifting', 'Alternate between a near object and something 10–20 feet away.'],
                    ['Screen Distance', "Keep your screen about an arm's length away from your eyes."],
                ],
            ],
            [
                'key' => 'light', 'icon' => 'sunny', 'title' => 'Light Sensitivity',
                'summary' => 'Understand what causes discomfort in bright light and how to manage it.',
                'meta' => [
                    'hero_title' => 'Light Sensitivity (Photophobia)',
                    'subtitle' => 'A common symptom of Dry Eye Syndrome affecting daily activities.',
                    'impact_title' => 'What is Photophobia?',
                    'impact' => 'Light sensitivity or photophobia is when your eyes become uncomfortable or painful when exposed to normal levels of light. This is particularly common in people with Dry Eye Syndrome.',
                    'symptoms_title' => 'Common Symptoms',
                    'prompt_title' => 'Sensitive to bright light or screens?',
                    'prompt_text' => 'Discover common triggers and how to manage light sensitivity effectively.',
                    'prompt_cancel' => 'Cancel',
                    'prompt_confirm' => 'View Info',
                ],
                'symptoms' => [
                    ['eye', 'Eye Discomfort', 'Pain or burning sensation', false],
                    ['sad', 'Bright Light Sensitivity', 'Difficulty with sunlight or bright indoor light', false],
                    ['medical', 'Screen Glare Issues', 'Difficulty using computers or phones', false],
                    ['eye-off', 'Blurred Vision', 'Trouble focusing on digital screens', true],
                ],
                'tips' => [
                    ['Adjust Brightness', 'Match your screen brightness to the lighting in your room.'],
                    ['Use Dark Mode', 'Switch to dark mode when you are in a dim environment.'],
                    ['Reduce Glare', 'Position your screen away from windows and direct overhead lights.'],
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function exercises(): array
    {
        $exercise = fn (string $key, string $icon, string $name, string $card, string $description, string $page, int $seconds, string $why, string $whyText, string $tip): array => [
            'key' => $key, 'icon' => $icon, 'name' => $name, 'description' => $description,
            'meta' => ['card_title' => $card, 'page_title' => $page, 'seconds' => $seconds, 'why_title' => $why, 'why_text' => $whyText, 'tip' => $tip],
        ];

        return [
            $exercise('palming', 'hand-left', 'Eye Palming', 'Eye Palming', 'Cover your eyes with your palms to relax them', 'Eye Palming Exercise', 60, 'Why Do Eye Palming Exercises?', 'Gently covering your eyes with your palms helps relax eye muscles and reduce tension from extended screen time.', 'Rub your hands together to warm them up before palming — the warmth enhances relaxation.'),
            $exercise('blinking', 'eye', 'Eye Blinking', 'Eye Blinking', 'Blink slowly to keep your eyes moist', 'Eye Blinking Exercise', 30, 'Why Blink Eye Exercises Matter?', 'Blinking helps spread moisture across your eyes and prevents dryness caused by long screen time.', 'Try to blink every 4–6 seconds while using your device to keep your eyes refreshed.'),
            $exercise('focus', 'locate', 'Focus Shifting', 'Focus Shift', 'Switch focus between near and far objects', 'Focus Shift Exercise', 30, 'Why Do Focus Shift Exercises?', 'Shifting focus between near and far objects helps your eyes relax and maintain flexibility, especially after long periods of screen use.', 'Every 30 minutes, try looking at something 30 feet away for 30 seconds — it gives your eyes a well-deserved reset.'),
            $exercise('rule', 'time', '30-30-30 Rule', '30-30-30 Rule', 'Look 30 feet away for 30 seconds', '30-30-30 Rule Exercise', 30, 'Why the 30-30-30 Rule Matters?', 'Looking 30 feet away for 30 seconds every 30 minutes helps reduce eye strain and gives your focusing muscles a chance to relax.', 'Set a reminder so you remember to look away while you work.'),
            $exercise('rolling', 'refresh', 'Eye Rolling', 'Eye Rolling', 'Roll your eyes in slow circles to ease tension', 'Eye Rolling Relaxation', 30, 'Why Eye Rolling Exercises Matter?', 'Rolling your eyes gently helps relax strained eye muscles, improve flexibility, and reduce tension built up from focusing on a screen.', 'Move slowly and keep your head still — only your eyes should move.'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function results(): array
    {
        return [
            [
                'key' => 'LOW', 'label' => 'Mild Eye Strain',
                'message' => 'Good job! Your eyes are doing okay. Keep following healthy screen habits.',
                'meta' => ['tips_title' => 'Take Care of Your Eyes', 'tips_intro' => "You're doing well. Keep these healthy habits going!", 'note' => ''],
                'tips' => [
                    ['eye', 'Blink more often during screen time', 'Remember to blink frequently to keep your eyes moist and reduce dryness while using digital devices.'],
                    ['time', 'Follow the 30-30-30 rule', 'Every 30 minutes, look at something 30 feet away for at least 30 seconds to give your eyes a break.'],
                    ['resize', "Keep your screen an arm's length away", 'Position your screen about 20-26 inches from your eyes to reduce strain and maintain proper posture.'],
                    ['moon', 'Use dark mode during low- light study', 'Switch to dark mode when studying in dimly lit environments to reduce screen brightness and eye strain.'],
                ],
            ],
            [
                'key' => 'MEDIUM', 'label' => 'Moderate Eye Strain',
                'message' => "Be careful! You're starting to feel the effects of DES. Try more frequent breaks and follow our tips.",
                'meta' => ['tips_title' => 'Support Your Eye Health', 'tips_intro' => 'You may be starting to feel screen strain. Try these to reduce discomfort.', 'note' => ''],
                'tips' => [
                    ['time', 'Set Screen Break Reminders', 'Take a 20-second break every 30 minutes to rest your eyes and reduce strain.'],
                    ['refresh', 'Try Eye Rolling Exercises', 'Perform gentle eye movements daily to strengthen eye muscles and improve circulation.'],
                    ['sunny', 'Adjust screen brightness', 'Match your screen brightness to your room lighting to reduce eye strain.'],
                ],
            ],
            [
                'key' => 'HIGH', 'label' => 'Severe Eye Strain',
                'message' => 'Your eyes are under a lot of strain. Follow care tips immediately and consider reducing your screen time.',
                'meta' => ['tips_title' => 'Take Action Now!', 'tips_intro' => 'Your eyes are showing signs of high strain. Follow these steps right away.', 'note' => 'If symptoms persist or worsen, consider consulting an eye care professional.'],
                'tips' => [
                    ['desktop', 'Reduce Screen Time', 'Take frequent breaks and limit unnecessary screen exposure whenever possible.'],
                    ['eye', 'Daily Eye Exercises', 'Practice blinking and focus shift exercises daily to strengthen your eye muscles.'],
                    ['moon', 'Enable Dark Mode', 'Switch to dark mode and reduce screen contrast to minimize eye strain.'],
                    ['time', '30-30-30 Rule', 'Every 30 minutes, look at something 30 feet away for at least 30 seconds.'],
                    ['bulb', 'Low-Light Rest', 'Rest in dim lighting to reduce light sensitivity and give your eyes time to recover.'],
                ],
            ],
        ];
    }
};
