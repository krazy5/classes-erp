<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassGroup;
use App\Models\Enquiry;
use App\Models\Student;
use App\Models\Subject;
use App\Support\StudentDashboardData;
use App\Support\UpcomingBirthdays;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        abort_unless($user->can('dashboard.view.reception'), 403);

        $tenantId = $user->tenant_id;

        $classGroups = ClassGroup::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderBy('name')
            ->get();

        $selectedClassGroupId = (int) $request->input('class_group_id');
        if ($classGroups->isNotEmpty() && !$classGroups->contains('id', $selectedClassGroupId)) {
            $selectedClassGroupId = (int) optional($classGroups->first())->id;
        }

        $selectedDateInput = $request->input('date', now()->toDateString());

        try {
            $selectedDate = Carbon::parse($selectedDateInput)->toDateString();
        } catch (\Throwable $exception) {
            $selectedDate = now()->toDateString();
        }

        $students = collect();
        $attendanceRecords = collect();

        if ($selectedClassGroupId) {
            $students = Student::query()
                ->where('class_group_id', $selectedClassGroupId)
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->orderBy('name')
                ->get();

            if ($students->isNotEmpty()) {
                $attendanceRecords = Attendance::query()
                    ->whereIn('student_id', $students->pluck('id'))
                    ->whereDate('date', $selectedDate)
                    ->get()
                    ->keyBy('student_id');
            }
        }

        $recentEnquiries = Enquiry::query()
            ->with('classGroup')
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->latest()
            ->limit(6)
            ->get();

        $enquiryStats = [
            'total' => Enquiry::query()->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))->count(),
            'open' => Enquiry::query()->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))->whereNull('closed_at')->count(),
            'followUps' => Enquiry::query()->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))->whereNotNull('follow_up_at')->whereNull('closed_at')->count(),
        ];

        $subjects = Subject::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderBy('name')
            ->get();

        $studentProfile = $user->studentProfile;
        $studentData = null;

        if ($studentProfile) {
            $studentData = StudentDashboardData::make($studentProfile, $tenantId);
        }

        $upcomingBirthdays = UpcomingBirthdays::forTenant(
            $tenantId,
            daysAhead: 21,
            classGroupIds: null,
            limit: 8
        );

        return view('reception.dashboard', [
            'user' => $user,
            'classGroups' => $classGroups,
            'selectedClassGroupId' => $selectedClassGroupId,
            'selectedDate' => $selectedDate,
            'students' => $students,
            'attendanceRecords' => $attendanceRecords,
            'recentEnquiries' => $recentEnquiries,
            'enquiryStats' => $enquiryStats,
            'subjects' => $subjects,
            'canRecordAttendance' => $user->can('attendance.record.class'),
            'canManageEnquiry' => $user->can('enquiry.manage'),
            'studentData' => $studentData,
            'studentProfile' => $studentProfile,
            'upcomingBirthdays' => $upcomingBirthdays,
        ]);
    }
}
