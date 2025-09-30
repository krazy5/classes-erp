<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();

        if (!$tenant) {
            return;
        }

        $students = Student::where('tenant_id', $tenant->id)->get();

        if ($students->isEmpty()) {
            return;
        }

        $dates = collect(range(0, 9))->map(fn (int $offset) => now()->subDays($offset)->toDateString());

        $students->each(function (Student $student) use ($dates, $tenant) {
            foreach ($dates as $date) {
                Attendance::updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'student_id' => $student->id,
                        'date' => $date,
                    ],
                    [
                        'present' => (bool) random_int(0, 1),
                    ]
                );
            }
        });
    }
}
