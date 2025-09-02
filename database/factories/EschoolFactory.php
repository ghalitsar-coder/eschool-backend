<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Eschool;
use App\Models\School;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory&lt;\App\Models\Eschool&gt;
 */
class EschoolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array&lt;string, mixed&gt;
     */
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'coordinator_id' => User::factory(),
            'treasurer_id' => User::factory(),
            'name' => fake()->word() . ' Club',
            'description' => fake()->sentence(),
            'monthly_kas_amount' => fake()->numberBetween(10000, 50000),
            'total_schedule_days' => fake()->numberBetween(1, 5),
            'is_active' => true,
        ];
    }
}