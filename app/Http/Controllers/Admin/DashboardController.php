<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\FeeRecord;
use App\Models\FeeStructure;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $tenantId = auth()->user()->tenant_id;
        $now = now();
        $startOfDay = $now->copy()->startOfDay();

        $baseStudentQuery = Student::when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId));
        $baseEnquiryQuery = Enquiry::when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId));
        $baseFeeRecordQuery = FeeRecord::when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId));
        $baseFeeStructureQuery = FeeStructure::when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId));

        $studentCount = (clone $baseStudentQuery)->count();
        $newStudentsThisMonth = (clone $baseStudentQuery)
            ->where('created_at', '>=', $now->copy()->startOfMonth())
            ->count();

        $openEnquiries = (clone $baseEnquiryQuery)
            ->whereNotIn('status', ['converted', 'lost'])
            ->count();
        $recentEnquiries = (clone $baseEnquiryQuery)
            ->latest()
            ->take(5)
            ->get();
        $upcomingFollowUps = (clone $baseEnquiryQuery)
            ->whereNotNull('follow_up_at')
            ->where('follow_up_at', '>=', $now)
            ->orderBy('follow_up_at')
            ->take(5)
            ->get();

        $feeOutstanding = (clone $baseFeeRecordQuery)
            ->where('is_paid', false)
            ->sum('total_amount');
        $collectedFeesThisMonth = (clone $baseFeeRecordQuery)
            ->where('is_paid', true)
            ->where('updated_at', '>=', $now->copy()->startOfMonth())
            ->sum('total_amount');

        $feeStructureCount = (clone $baseFeeStructureQuery)->count();
        $activeFeeStructures = (clone $baseFeeStructureQuery)
            ->where('is_active', true)
            ->orderBy('name')
            ->take(5)
            ->get();

        $upcomingBirthdays = (clone $baseStudentQuery)
            ->whereNotNull('dob')
            ->get()
            ->map(function (Student $student) use ($now, $startOfDay) {
                $dob = Carbon::parse($student->dob);
                $nextBirthday = $dob->copy()->year($now->year);
                if ($nextBirthday->isBefore($startOfDay)) {
                    $nextBirthday->addYear();
                }

                $daysUntilBirthday = $nextBirthday->diffInDays($startOfDay);

                $student->next_birthday = $nextBirthday;
                $student->turning_age = $dob->diffInYears($nextBirthday);
                $student->days_until_birthday = $daysUntilBirthday;

                return $student;
            })
            ->filter(fn (Student $student) => $student->days_until_birthday <= 30)
            ->sortBy('days_until_birthday')
            ->take(5)
            ->values();

        return view('admin.dashboard', [
            'studentCount' => $studentCount,
            'newStudentsThisMonth' => $newStudentsThisMonth,
            'openEnquiries' => $openEnquiries,
            'recentEnquiries' => $recentEnquiries,
            'upcomingFollowUps' => $upcomingFollowUps,
            'upcomingBirthdays' => $upcomingBirthdays,
            'feeOutstanding' => $feeOutstanding,
            'collectedFeesThisMonth' => $collectedFeesThisMonth,
            'feeStructureCount' => $feeStructureCount,
            'activeFeeStructures' => $activeFeeStructures,
        ]);
    }
}
