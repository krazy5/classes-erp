<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Mark;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();

        if (!$tenant) {
            return;
        }

        $subjects = Subject::where('tenant_id', $tenant->id)->get();
        $students = Student::where('tenant_id', $tenant->id)->get();

        if ($subjects->isEmpty() || $students->isEmpty()) {
            return;
        }

        $existingExams = Exam::where('tenant_id', $tenant->id)->get();
        $desiredExams = 2;
        $toCreate = max(0, $desiredExams - $existingExams->count());

        if ($toCreate > 0) {
            $existingExams = $existingExams->merge(
                Exam::factory()->count($toCreate)->create(['tenant_id' => $tenant->id])
            );
        }

        $existingExams->each(function (Exam $exam) use ($subjects, $students, $tenant) {
            $selectedSubjects = collect($subjects->random(min(3, $subjects->count())));

            $selectedSubjects->each(function (Subject $subject) use ($exam, $students, $tenant) {
                $examSubject = ExamSubject::firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'exam_id' => $exam->id,
                        'subject_id' => $subject->id,
                    ]
                );

                $sampleSize = min(15, $students->count());
                $sample = collect($students->random($sampleSize));

                $sample->each(function (Student $student) use ($examSubject, $tenant) {
                    Mark::updateOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'exam_subject_id' => $examSubject->id,
                            'student_id' => $student->id,
                        ],
                        [
                            'score' => random_int(35, 100),
                        ]
                    );
                });
            });
        });
    }
}
