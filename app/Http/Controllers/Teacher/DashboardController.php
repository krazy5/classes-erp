<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassGroup;
use App\Models\Student;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use App\Support\UpcomingBirthdays;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        abort_unless($user->can('dashboard.view.teacher'), 403);

        $teacher = $user->teacherProfile;

        if (!$teacher) {
            abort(404, 'Teacher profile not found.');
        }

        $tenantId = $user->tenant_id;

        $timetables = Timetable::with(['classGroup', 'subject'])
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->where('teacher_id', $teacher->id)
            ->get();

        $dayOrder = [
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6,
            'Sunday' => 7,
        ];

        $timetableByDay = $timetables
            ->groupBy('day_of_week')
            ->sortBy(fn ($entries, $day) => $dayOrder[$day] ?? 99)
            ->map(fn ($entries) => $entries->sortBy('start_time')->values());

        $classGroupIds = $timetables->pluck('class_group_id')->unique()->values();

        $classGroups = ClassGroup::query()
            ->whereIn('id', $classGroupIds)
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderBy('name')
            ->get();

        $selectedClassGroupId = (int) $request->input('class_group_id');
        if (!$selectedClassGroupId || !$classGroupIds->contains($selectedClassGroupId)) {
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
        $selectedClassGroup = null;

        if ($selectedClassGroupId) {
            $selectedClassGroup = $classGroups->firstWhere('id', $selectedClassGroupId);

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

        $totalStudents = $classGroupIds->isEmpty()
            ? 0
            : Student::query()
                ->whereIn('class_group_id', $classGroupIds)
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->count();

        $upcomingBirthdays = UpcomingBirthdays::forTenant(
            $tenantId,
            daysAhead: 21,
            classGroupIds: $classGroupIds->filter()->all(),
            limit: 6
        );

        return view('teacher.dashboard', [
            'teacher' => $teacher,
            'timetableByDay' => $timetableByDay,
            'classGroups' => $classGroups,
            'selectedClassGroupId' => $selectedClassGroupId,
            'selectedClassGroup' => $selectedClassGroup,
            'selectedDate' => $selectedDate,
            'students' => $students,
            'attendanceRecords' => $attendanceRecords,
            'canRecordAttendance' => $user->can('attendance.record.class'),
            'totalStudents' => $totalStudents,
            'weeklySessions' => $timetables->count(),
            'upcomingBirthdays' => $upcomingBirthdays,
        ]);
    }
}
