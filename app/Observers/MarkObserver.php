<?php

namespace App\Observers;

use App\Models\Mark;
use App\Notifications\MarksPublishedNotification;
use Illuminate\Support\Facades\Notification;

class MarkObserver
{
    public function created(Mark $mark): void
    {
        $this->notifyGuardians($mark);
    }

    public function updated(Mark $mark): void
    {
        $this->notifyGuardians($mark);
    }

    protected function notifyGuardians(Mark $mark): void
    {
        $student = $mark->student;

        if (!$student || !$student->user) {
            return;
        }

        $guardians = $student->user->guardians;

        if ($guardians->isEmpty()) {
            return;
        }

        $examName = optional(optional($mark->examSubject)->exam)->title ?? 'recent assessments';

        Notification::send($guardians, new MarksPublishedNotification($student->name, $examName));
    }
}
