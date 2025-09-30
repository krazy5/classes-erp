<?php

namespace Database\Factories;

use App\Models\ClassGroup;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimetableFactory extends Factory
{
    protected $model = Timetable::class;

    public function definition(): array
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return [
            'tenant_id' => null,
            'class_group_id' => ClassGroup::factory(),
            'subject_id' => Subject::factory(),
            'teacher_id' => Teacher::factory(),
            'day_of_week' => $this->faker->randomElement($days),
            'start_time' => $this->faker->time('H:i'),
            'end_time' => $this->faker->time('H:i'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
