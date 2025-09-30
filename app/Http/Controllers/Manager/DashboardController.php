<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Enquiry;
use App\Models\FeeRecord;
use App\Models\Installment;
use App\Models\Student;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfWeek = $now->copy()->startOfDay()->subDays(6);

        $studentsQuery = Student::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId));

        $studentCount = (clone $studentsQuery)->count();
        $newStudentsThisMonth = (clone $studentsQuery)
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        $feeRecordQuery = FeeRecord::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId));

        $feeOutstanding = (clone $feeRecordQuery)
            ->where('is_paid', false)
            ->sum('total_amount');
        $feeOutstandingCount = (clone $feeRecordQuery)
            ->where('is_paid', false)
            ->count();
        $feesCollectedThisMonth = (clone $feeRecordQuery)
            ->where('is_paid', true)
            ->where('updated_at', '>=', $startOfMonth)
            ->sum('total_amount');

        $baseInstallments = Installment::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->outstanding()
            ->whereNotNull('due_date');

        $upcomingInstallmentsQuery = (clone $baseInstallments)
            ->whereBetween('due_date', [$now->copy()->startOfDay(), $now->copy()->addDays(7)->endOfDay()]);
        $upcomingInstallmentCount = (clone $upcomingInstallmentsQuery)->count();
        $upcomingInstallments = $upcomingInstallmentsQuery
            ->with(['feeRecord.student'])
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $overdueInstallmentsQuery = (clone $baseInstallments)
            ->where('due_date', '<', $now->copy()->startOfDay());
        $overdueInstallmentCount = (clone $overdueInstallmentsQuery)->count();
        $overdueInstallments = $overdueInstallmentsQuery
            ->with(['feeRecord.student'])
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $enquiryQuery = Enquiry::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId));

        $openEnquiries = (clone $enquiryQuery)
            ->whereNotIn('status', ['converted', 'lost'])
            ->count();

        $followUpBase = (clone $enquiryQuery)
            ->whereNotNull('follow_up_at')
            ->whereNull('closed_at');

        $upcomingFollowUpsQuery = (clone $followUpBase)
            ->whereBetween('follow_up_at', [$now, $now->copy()->addDays(7)]);
        $upcomingFollowUpsCount = (clone $upcomingFollowUpsQuery)->count();
        $upcomingFollowUps = $upcomingFollowUpsQuery
            ->with('assignee')
            ->orderBy('follow_up_at')
            ->limit(5)
            ->get();

        $overdueFollowUpsQuery = (clone $followUpBase)
            ->where('follow_up_at', '<', $now);
        $overdueFollowUpsCount = (clone $overdueFollowUpsQuery)->count();
        $overdueFollowUps = $overdueFollowUpsQuery
            ->with('assignee')
            ->orderBy('follow_up_at')
            ->limit(5)
            ->get();

        $attendanceSummary = Attendance::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->where('date', '>=', $startOfWeek)
            ->selectRaw('date, SUM(CASE WHEN present = 1 THEN 1 ELSE 0 END) as present_count, SUM(CASE WHEN present = 0 THEN 1 ELSE 0 END) as absent_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => Carbon::parse($row->date)->format('d M'),
                'present' => (int) $row->present_count,
                'absent' => (int) $row->absent_count,
                'total' => (int) $row->present_count + (int) $row->absent_count,
            ]);

        $todayClasses = Timetable::with(['classGroup', 'subject', 'teacher'])
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->where('day_of_week', $now->format('l'))
            ->orderBy('start_time')
            ->limit(6)
            ->get();

        $latestAnnouncements = Announcement::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('manager.dashboard', [
            'studentCount' => $studentCount,
            'newStudentsThisMonth' => $newStudentsThisMonth,
            'feeOutstanding' => (float) $feeOutstanding,
            'feeOutstandingCount' => $feeOutstandingCount,
            'feesCollectedThisMonth' => (float) $feesCollectedThisMonth,
            'upcomingInstallments' => $upcomingInstallments,
            'upcomingInstallmentCount' => $upcomingInstallmentCount,
            'overdueInstallments' => $overdueInstallments,
            'overdueInstallmentCount' => $overdueInstallmentCount,
            'openEnquiries' => $openEnquiries,
            'upcomingFollowUps' => $upcomingFollowUps,
            'upcomingFollowUpsCount' => $upcomingFollowUpsCount,
            'overdueFollowUps' => $overdueFollowUps,
            'overdueFollowUpsCount' => $overdueFollowUpsCount,
            'attendanceSummary' => $attendanceSummary,
            'todayClasses' => $todayClasses,
            'latestAnnouncements' => $latestAnnouncements,
        ]);
    }
}
