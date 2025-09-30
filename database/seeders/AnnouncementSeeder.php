<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\ClassGroup;
use App\Models\Student;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();

        if (!$tenant) {
            return;
        }

        $classGroups = ClassGroup::where('tenant_id', $tenant->id)->get();
        $students = Student::where('tenant_id', $tenant->id)->get();

        if ($classGroups->isEmpty() && $students->isEmpty()) {
            return;
        }

        $announcements = Announcement::factory()->count(3)->create([
            'tenant_id' => $tenant->id,
        ]);

        $announcements->each(function (Announcement $announcement) use ($classGroups, $students) {
            if ($classGroups->isNotEmpty()) {
                $selectedGroups = collect($classGroups->random(min(2, $classGroups->count())));
                $announcement->classGroups()->syncWithoutDetaching($selectedGroups->pluck('id')->all());
            }

            if ($students->isNotEmpty()) {
                $selectedStudents = collect($students->random(min(10, $students->count())));
                $announcement->students()->syncWithoutDetaching($selectedStudents->pluck('id')->all());
            }
        });
    }
}
