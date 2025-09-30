<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Tenant;
use App\Models\Timetable;
use Illuminate\Database\Seeder;

class TimetableSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();

        if (!$tenant) {
            return;
        }

        $classGroups = ClassGroup::where('tenant_id', $tenant->id)->get();
        $subjects = Subject::where('tenant_id', $tenant->id)->get();
        $teachers = Teacher::where('tenant_id', $tenant->id)->get();

        if ($classGroups->isEmpty() || $subjects->isEmpty() || $teachers->isEmpty()) {
            return;
        }

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        $classGroups->each(function (ClassGroup $class) use ($subjects, $teachers, $days, $tenant) {
            foreach ($days as $day) {
                for ($slotIndex = 0; $slotIndex < 3; $slotIndex++) {
                    $subject = $subjects->random();
                    $teacher = $teachers->random();
                    $start = now()->startOfDay()->addHours(8 + ($slotIndex * 2));

                    Timetable::updateOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'class_group_id' => $class->id,
                            'day_of_week' => $day,
                            'start_time' => $start->format('H:i'),
                        ],
                        [
                            'subject_id' => $subject->id,
                            'teacher_id' => $teacher->id,
                            'end_time' => $start->copy()->addHour()->format('H:i'),
                        ]
                    );
                }
            }
        });
    }
}
