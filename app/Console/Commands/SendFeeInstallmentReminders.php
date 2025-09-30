<?php

namespace App\Console\Commands;

use App\Models\Installment;
use App\Notifications\FeeInstallmentReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'erp:send-installment-reminders', description: 'Send reminder notifications to guardians for upcoming fee installments.')]
class SendFeeInstallmentReminders extends Command
{
    protected $signature = 'erp:send-installment-reminders {--days=3 : Number of days ahead to look for due installments}';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoffStart = Carbon::today();
        $cutoffEnd = Carbon::today()->addDays(max(0, $days));

        $installments = Installment::query()
            ->with(['feeRecord.student.user.guardians'])
            ->whereNull('paid_at')
            ->whereBetween('due_date', [$cutoffStart, $cutoffEnd])
            ->get();

        $count = 0;

        foreach ($installments as $installment) {
            $studentUser = optional($installment->feeRecord->student)->user;

            if (!$studentUser) {
                continue;
            }

            $guardians = $studentUser->guardians;

            if ($guardians->isEmpty()) {
                continue;
            }

            Notification::send($guardians, new FeeInstallmentReminderNotification($installment));
            $count += $guardians->count();
        }

        $this->info("Sent {$count} reminders for installments due between {$cutoffStart->toDateString()} and {$cutoffEnd->toDateString()}.");

        return Command::SUCCESS;
    }
}
