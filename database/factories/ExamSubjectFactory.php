<?php

namespace Database\Factories;

use App\Models\ExamSubject;
use App\Models\Exam;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamSubjectFactory extends Factory
{
    protected $model = ExamSubject::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'exam_id' => Exam::factory(),
            'subject_id' => Subject::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
