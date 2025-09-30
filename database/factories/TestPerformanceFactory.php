<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\Subject;
use App\Models\TestPerformance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestPerformanceFactory extends Factory
{
    protected $model = TestPerformance::class;

    public function definition(): array
    {
        $maxScore = $this->faker->randomElement([50, 75, 100]);
        $score = $this->faker->numberBetween((int) ($maxScore * 0.3), $maxScore);

        return [
            'tenant_id' => null,
            'student_id' => Student::factory(),
            'subject_id' => Subject::factory(),
            'recorded_by' => User::factory(),
            'title' => $this->faker->randomElement(['Unit Test', 'Monthly Test', 'Surprise Quiz', 'Assessment']) . ' ' . $this->faker->numberBetween(1, 5),
            'assessment_type' => $this->faker->randomElement(['Quiz', 'Oral', 'Practical', 'Written']),
            'term' => $this->faker->randomElement(['Term 1', 'Term 2', 'Mid Term', 'Final']),
            'test_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'max_score' => $maxScore,
            'score' => $score,
            'percentage' => round(($score / $maxScore) * 100, 2),
            'grade' => null,
            'metadata' => [
                'weightage' => $this->faker->randomElement([10, 20, 25]),
            ],
            'remarks' => $this->faker->sentence(),
        ];
    }
}
