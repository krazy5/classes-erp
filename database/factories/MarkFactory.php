<?php

namespace Database\Factories;

use App\Models\ExamSubject;
use App\Models\Mark;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class MarkFactory extends Factory
{
    protected $model = Mark::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'exam_subject_id' => ExamSubject::factory(),
            'student_id' => Student::factory(),
            'score' => $this->faker->numberBetween(0, 100),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
