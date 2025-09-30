<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExamScheduleNotification extends Notification
{
    use Queueable;

    public function __construct(protected string $studentName, protected string $examName, protected string $examDate)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Upcoming exam: ' . $this->examName)
            ->greeting('Hello ' . $notifiable->name)
            ->line("{$this->studentName} has {$this->examName} scheduled on {$this->examDate}.")
            ->line('Please ensure the student is prepared and arrives on time.')
            ->action('View exam schedule', route('parent.dashboard'))
            ->line('Good luck to your child!');
    }
}
