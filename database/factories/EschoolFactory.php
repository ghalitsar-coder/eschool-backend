<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Eschool;
use App\Models\School;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Eschool>
 */
class EschoolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'name' => fake()->word() . ' Club',
            'schedule_days' => fake()->dayOfWeek,
            'description' => fake()->sentence(),
            'is_active' => true,
            'monthly_fee_amount' => fake()->numberBetween(10000, 50000),
        ];
    }
}