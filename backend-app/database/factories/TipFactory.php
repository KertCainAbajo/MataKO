<?php

namespace Database\Factories;

use App\Models\Tip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tip>
 */
class TipFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'audience' => 'all',
            'kind' => 'fact',
            'icon' => 'bulb',
            'title' => null,
            'body' => fake()->sentence(),
            'position' => fake()->numberBetween(1, 50),
            'is_active' => true,
        ];
    }
}
