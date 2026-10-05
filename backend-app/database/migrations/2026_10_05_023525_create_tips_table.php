<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Editable Tips content. Kinds: "category" (a Tips section), "tip" (an item inside a category),
     * "daily_tip" (Today's Eye Tip, rotated by day) and "fact" (Did You Know?).
     */
    public function up(): void
    {
        Schema::create('tips', function (Blueprint $table) {
            $table->id();
            $table->string('audience', 20);
            $table->string('kind', 20);
            $table->foreignId('parent_id')->nullable()->constrained('tips')->cascadeOnDelete();
            $table->string('icon', 40)->nullable();
            $table->string('color', 7)->nullable();
            $table->string('title')->nullable();
            $table->text('body');
            $table->unsignedSmallInteger('position')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['kind', 'audience', 'position']);
        });

        $now = now();
        $insert = fn (array $row): int => DB::table('tips')->insertGetId($row + ['created_at' => $now, 'updated_at' => $now, 'is_active' => true]);

        foreach ($this->initialCategories() as $audience => $categories) {
            foreach ($categories as $categoryIndex => [$icon, $title, $body, $tips]) {
                $categoryId = $insert(['audience' => $audience, 'kind' => 'category', 'icon' => $icon, 'title' => $title, 'body' => $body, 'position' => $categoryIndex + 1]);
                foreach ($tips as $tipIndex => [$tipIcon, $color, $tipTitle, $tipBody]) {
                    $insert(['audience' => $audience, 'kind' => 'tip', 'parent_id' => $categoryId, 'icon' => $tipIcon, 'color' => $color, 'title' => $tipTitle, 'body' => $tipBody, 'position' => $tipIndex + 1]);
                }
            }
        }

        $insert(['audience' => 'all', 'kind' => 'daily_tip', 'body' => 'Remember to blink more often while using your screen. The 30-30-30 rule can help reduce eye strain.', 'position' => 1]);
        $insert(['audience' => 'all', 'kind' => 'fact', 'icon' => 'alert-circle', 'body' => 'Staring at a screen reduces your blink rate by up to 60%!', 'position' => 1]);
        $insert(['audience' => 'all', 'kind' => 'fact', 'icon' => 'moon', 'body' => 'Blue light exposure before sleep can delay melatonin release', 'position' => 2]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tips');
    }

    /**
     * @return array<string, list<array{0: string, 1: string, 2: string, 3: list<array{0: string, 1: string, 2: string, 3: string}>}>>
     */
    private function initialCategories(): array
    {
        $lifestyle = ['heart', 'Lifestyle & Wellness', 'Balance screen-heavy routines with good sleep, eye-friendly foods, and physical activity.', [
            ['nutrition', '#16A34A', 'Eat Eye-Healthy Foods', 'Include carrots, leafy greens, and fish rich in omega-3 in your diet.'],
            ['barbell', '#7A5AF8', 'Eye Exercises', 'Practice simple eye movements and focus exercises daily.'],
            ['person', '#2E90FA', 'Regular Check-ups', 'Visit an eye doctor annually for comprehensive eye examinations.'],
        ]];
        $hydrated = ['water', '#2E90FA', 'Stay Hydrated', 'Drink 8 glasses of water daily to keep your eyes naturally lubricated.'];
        $sunglasses = ['sunny', '#F5C518', 'Wear Sunglasses', 'Protect your eyes from UV rays when outdoors, even on cloudy days.'];
        $sleep = ['moon', '#7A5AF8', 'Get Quality Sleep', 'Aim for 7–8 hours of sleep to allow your eyes to rest and recover.'];
        $rule = ['time', '#16A34A', '30-30-30 Rule', 'Every 30 minutes, look at something 30 feet away for 30 seconds.'];

        return [
            'student' => [
                ['eye', 'Daily Eye Care', 'Simple habits to keep your eyes healthy during class, study, and screen time.', [$hydrated, $sunglasses, $sleep]],
                ['desktop', 'Screen Use', 'Manage screen brightness, reduce glare, and use blue light filters while studying.', [
                    $rule,
                    ['sunny', '#98A2B3', 'Adjust Brightness', 'Match your screen brightness to your study area so it is not brighter than the room.'],
                    ['glasses', '#2E90FA', 'Use a Blue Light Filter', 'Turn on night mode or a blue light filter during evening study sessions.'],
                ]],
                ['book', 'Study Habits', 'Prevent eye strain with proper posture, printed notes, and timed visual breaks.', [
                    ['body', '#2E90FA', 'Keep Good Posture', 'Sit upright with your screen slightly below eye level and about an arm’s length away.'],
                    ['document-text', '#F5C518', 'Use Printed Notes', 'Review printed notes or books when you can to give your eyes a break from screens.'],
                    ['alarm', '#E5121B', 'Take Timed Breaks', 'Rest your eyes for a few minutes after every study session or online class.'],
                ]],
                $lifestyle,
            ],
            'professional' => [
                ['eye', 'Daily Eye Care', 'Easy techniques to reduce eye fatigue throughout your workday.', [$hydrated, $sunglasses, $sleep]],
                ['desktop', 'Screen Use', 'Adjust screen settings and lighting for long office or remote work sessions.', [
                    $rule,
                    ['sunny', '#98A2B3', 'Adjust Brightness', 'Match your screen brightness to your surrounding environment.'],
                    ['resize', '#2E90FA', 'Proper Distance', 'Keep screens 20-26 inches away and slightly below eye level.'],
                ]],
                ['briefcase', 'Work Habits', 'Stay productive without sacrificing your eyes—use scheduled breaks and ergonomic setups.', [
                    ['bulb', '#F5C518', 'Good Lighting', 'Use adequate lighting when reading to reduce eye strain and fatigue.'],
                    ['pause', '#E5121B', 'Take Regular Breaks', 'Step away from your work every hour for a few minutes of rest.'],
                ]],
                $lifestyle,
            ],
        ];
    }
};
