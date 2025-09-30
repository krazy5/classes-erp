<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MarksPublishedNotification extends Notification
{
    use Queueable;

    public function __construct(protected string $studentName, protected string $examName)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Marks published: ' . $this->examName)
            ->greeting('Hello ' . $notifiable->name)
            ->line("Marks for {$this->studentName} in {$this->examName} have been published.")
            ->action('View marks', route('parent.dashboard'))
            ->line('Please review and reach out if you have any questions.');
    }
}
