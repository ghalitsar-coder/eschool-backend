<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\School;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory&lt;\App\Models\School&gt;
 */
class SchoolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array&lt;string, mixed&gt;
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}