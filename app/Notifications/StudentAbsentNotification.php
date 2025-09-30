<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentAbsentNotification extends Notification
{
    use Queueable;

    public function __construct(protected string $studentName, protected string $date)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Attendance alert: ' . $this->studentName)
            ->greeting('Hello ' . $notifiable->name)
            ->line("We noticed that {$this->studentName} was marked absent on {$this->date}.")
            ->line('Please reach out to the school if you have any questions or would like to provide a reason for the absence.')
            ->action('View attendance', route('parent.dashboard'))
            ->line('Thank you for staying engaged with your child’s progress.');
    }
}
