<?php

namespace Database\Factories;

use App\Models\Exam;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamFactory extends Factory
{
    protected $model = Exam::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'title' => $this->faker->randomElement([
                'Midterm Exam', 'Final Exam', 'Unit Test', 'Prelims'
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
