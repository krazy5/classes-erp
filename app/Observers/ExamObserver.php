<?php

namespace App\Observers;

use App\Models\Exam;
use App\Models\Student;
use App\Notifications\ExamScheduleNotification;
use Illuminate\Support\Facades\Notification;

class ExamObserver
{
    public function created(Exam $exam): void
    {
        $this->notifyGuardians($exam);
    }

    public function updated(Exam $exam): void
    {
        if ($exam->wasChanged(['scheduled_at', 'title', 'class_group_id'])) {
            $this->notifyGuardians($exam);
        }
    }

    protected function notifyGuardians(Exam $exam): void
    {
        $classGroupId = $exam->class_group_id;

        $students = Student::query()
            ->when($classGroupId, fn ($query) => $query->where('class_group_id', $classGroupId))
            ->where('tenant_id', $exam->tenant_id)
            ->with('user.guardians')
            ->get();

        foreach ($students as $student) {
            $user = $student->user;

            if (!$user) {
                continue;
            }

            $guardians = $user->guardians;

            if ($guardians->isEmpty()) {
                continue;
            }

            $date = optional($exam->scheduled_at)->format('d M Y') ?? 'soon';

            Notification::send($guardians, new ExamScheduleNotification($student->name, $exam->title, $date));
        }
    }
}
