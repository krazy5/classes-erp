<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'name' => $this->faker->randomElement([
                'Mathematics', 'Physics', 'Chemistry', 'Biology', 'English', 'History'
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
