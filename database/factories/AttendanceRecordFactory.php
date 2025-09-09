<?php

namespace Database\Factories;

use App\Models\AttendanceRecord;
use App\Models\UserEschoolRole;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceRecordFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AttendanceRecord::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_eschool_role_id' => UserEschoolRole::factory(),
            'date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'status' => $this->faker->randomElement(['present', 'absent', 'late']),
            'notes' => $this->faker->optional()->sentence(),
            'proof_document' => null, // Will be set when needed
        ];
    }

    /**
     * Indicate that the attendance record is for a present member.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function present()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'present',
                'proof_document' => null,
            ];
        });
    }

    /**
     * Indicate that the attendance record is for an absent member.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function absent()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'absent',
                'proof_document' => 'attendance/proofs/sample_proof.jpg',
            ];
        });
    }

    /**
     * Indicate that the attendance record is for a late member.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function late()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'late',
                'proof_document' => null,
            ];
        });
    }
}