<?php

namespace App\Notifications;

use App\Models\Installment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeeInstallmentReminderNotification extends Notification
{
    use Queueable;

    public function __construct(protected Installment $installment)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $student = optional($this->installment->feeRecord->student)->name ?? 'your child';
        $dueDate = optional($this->installment->due_date)->format('M d, Y') ?? 'soon';

        return (new MailMessage)
            ->subject('Fee installment reminder')
            ->greeting('Hello ' . $notifiable->name)
            ->line("This is a reminder that {$student}'s fee installment is due on {$dueDate}.")
            ->line('Amount due: ₹' . number_format((float) $this->installment->amount, 2))
            ->action('View fee plan', route('parent.dashboard', ['student_user_id' => optional($this->installment->feeRecord->student->user)->id]))
            ->line('Thank you for your prompt attention.');
    }
}
