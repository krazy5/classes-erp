<?php

namespace Database\Factories;

use App\Models\FeeRecord;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeeRecordFactory extends Factory
{
    protected $model = FeeRecord::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'student_id' => Student::factory(),
            'fee_structure_id' => null,
            'total_amount' => $this->faker->randomFloat(2, 1000, 10000),
            'is_paid' => false,
            'notes' => $this->faker->optional()->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
