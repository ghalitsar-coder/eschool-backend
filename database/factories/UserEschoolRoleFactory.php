<?php

namespace Database\Factories;

use App\Models\UserEschoolRole;
use App\Models\User;
use App\Models\Eschool;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserEschoolRoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserEschoolRole::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'eschool_id' => Eschool::factory(),
            'role' => $this->faker->randomElement(['member', 'treasurer', 'coordinator']),
        ];
    }
}