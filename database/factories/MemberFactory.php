<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Member;
use App\Models\School;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory&lt;\App\Models\Member&gt;
 */
class MemberFactory extends Factory
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
            'user_id' => User::factory(),
            'nip' => fake()->unique()->numerify('NIP####'),
            'name' => fake()->name(),
            'student_id' => fake()->unique()->numerify('SID####'),
            'date_of_birth' => fake()->date(),
            'gender' => fake()->randomElement(['L', 'P']),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'status' => fake()->randomElement(['active', 'inactive']),
            'is_active' => true,
        ];
    }
}