<?php

namespace App\Support;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Timetable;
use Illuminate\Support\Carbon;

class StudentDashboardData
{
    public static function make(Student $student, ?int $tenantId = null): array
    {
        $classGroup = $student->classGroup;

        $dayOrder = [
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6,
            'Sunday' => 7,
        ];

        $timetableByDay = collect();

        if ($classGroup) {
            $timetables = Timetable::with(['subject', 'teacher'])
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->where('class_group_id', $classGroup->id)
                ->get();

            $timetableByDay = $timetables
                ->groupBy('day_of_week')
                ->sortBy(fn ($entries, $day) => $dayOrder[$day] ?? 99)
                ->map(fn ($entries) => $entries->sortBy('start_time')->values());
        }

        $attendanceSummaryRow = Attendance::query()
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN present = 1 THEN 1 ELSE 0 END) as present_count')
            ->where('student_id', $student->id)
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->first();

        $totalSessions = (int) ($attendanceSummaryRow->total ?? 0);
        $presentSessions = (int) ($attendanceSummaryRow->present_count ?? 0);
        $absentSessions = max(0, $totalSessions - $presentSessions);
        $attendancePercentage = $totalSessions > 0 ? round(($presentSessions / $totalSessions) * 100, 1) : null;

        $recentAttendance = Attendance::query()
            ->where('student_id', $student->id)
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $calendar = static::buildAttendanceCalendar($student, $tenantId);

        return [
            'classGroup' => $classGroup,
            'timetableByDay' => $timetableByDay,
            'attendanceSummary' => [
                'total' => $totalSessions,
                'present' => $presentSessions,
                'absent' => $absentSessions,
                'percentage' => $attendancePercentage,
            ],
            'recentAttendance' => $recentAttendance,
            'attendanceCalendar' => $calendar,
        ];
    }

    protected static function buildAttendanceCalendar(Student $student, ?int $tenantId = null): array
    {
        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();

        $records = Attendance::query()
            ->where('student_id', $student->id)
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get()
            ->keyBy(fn ($attendance) => $attendance->date->toDateString());

        $calendarStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

        $days = [];
        $cursor = $calendarStart->copy();

        while ($cursor->lte($calendarEnd)) {
            $dateKey = $cursor->toDateString();
            $attendance = $records->get($dateKey);

            $days[] = [
                'date' => $cursor->copy(),
                'in_month' => $cursor->isSameMonth($monthStart),
                'status' => $attendance ? ($attendance->present ? 'present' : 'absent') : null,
            ];

            $cursor->addDay();
        }

        return [
            'month_name' => $monthStart->format('F Y'),
            'days' => $days,
        ];
    }
}
