<?php

namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'audience' => 'student',
            'position' => fake()->numberBetween(1, 50),
            'symptom' => fake()->unique()->words(2, true),
            'question' => fake()->sentence().'?',
            'image' => null,
            'is_active' => true,
        ];
    }
}
