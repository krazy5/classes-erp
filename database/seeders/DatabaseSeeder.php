<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TenantSeeder::class,
            RolesSeeder::class,
            DemoUserSeeder::class,
            SubjectSeeder::class,
            ClassGroupSeeder::class,
            FeeStructureSeeder::class,
            TeacherSeeder::class,
            StudentSeeder::class,
            TimetableSeeder::class,
            AttendanceSeeder::class,
            FeeRecordSeeder::class,
            ExamSeeder::class,
            AnnouncementSeeder::class,
            EnquirySeeder::class,
        ]);
    }
}
