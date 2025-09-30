<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassGroup;
use App\Models\Student;
use App\Notifications\StudentAbsentNotification;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'class_group_id' => ['nullable', 'exists:class_groups,id'],
            'present' => ['nullable', 'in:present,absent'],
            'student_id' => ['nullable', 'exists:students,id'],
        ], [], [
            'class_group_id' => 'class group',
        ]);

        $attendancesQuery = Attendance::with(['student.classGroup', 'student.media'])
            ->when($tenantId, fn (Builder $query) => $query->where('tenant_id', $tenantId));

        $this->applyFilters($attendancesQuery, $filters);

        $attendances = $attendancesQuery
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $summaryQuery = Attendance::query()
            ->when($tenantId, fn (Builder $query) => $query->where('tenant_id', $tenantId));

        $this->applyFilters($summaryQuery, $filters);

        $summaryRow = $summaryQuery
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN present = 1 THEN 1 ELSE 0 END) as present_count')
            ->first();

        $summary = [
            'total' => (int) ($summaryRow->total ?? 0),
            'present' => (int) ($summaryRow->present_count ?? 0),
        ];
        $summary['absent'] = max(0, $summary['total'] - $summary['present']);

        return view('admin.attendances.index', [
            'attendances' => $attendances,
            'filters' => $filters,
            'classGroups' => $this->classGroups($request),
            'summary' => $summary,
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.attendances.create', [
            'attendance' => new Attendance(['date' => now()->toDateString()]),
            'students' => $this->students($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        Attendance::updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'date' => $data['date'],
            ],
            [
                'present' => $data['present'],
            ]
        );

        return redirect()->route('admin.attendances.index')
            ->with('status', 'Attendance saved.');
    }

    public function edit(Request $request, Attendance $attendance): View
    {
        $this->assertTenant($request, $attendance);

        return view('admin.attendances.edit', [
            'attendance' => $attendance,
            'students' => $this->students($request),
        ]);
    }

    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        $this->assertTenant($request, $attendance);
        $data = $this->validatedData($request);

        $attendance->update($data);

        return redirect()->route('admin.attendances.index')
            ->with('status', 'Attendance updated.');
    }

    public function destroy(Request $request, Attendance $attendance): RedirectResponse
    {
        $this->assertTenant($request, $attendance);
        $attendance->delete();

        return redirect()->route('admin.attendances.index')
            ->with('status', 'Attendance removed.');
    }

    public function entry(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $classGroups = $this->classGroups($request);
        $selectedDate = $request->input('date', now()->toDateString());
        $classGroupId = $request->input('class_group_id');

        $students = collect();

        if ($classGroupId) {
            $students = Student::with([
                'media',
                'attendances' => fn ($query) => $query->whereDate('date', $selectedDate),
            ])
                ->where('class_group_id', $classGroupId)
                ->when($tenantId, fn (Builder $query) => $query->where('tenant_id', $tenantId))
                ->orderBy('name')
                ->get();
        }

        $presentCount = $students->filter(function ($student) {
            return optional($student->attendances->first())->present;
        })->count();

        return view('admin.attendances.entry', [
            'classGroups' => $classGroups,
            'students' => $students,
            'selectedDate' => $selectedDate,
            'selectedClassGroup' => $classGroupId,
            'presentCount' => $presentCount,
        ]);
    }

    public function entryStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'class_group_id' => ['required', 'exists:class_groups,id'],
            'students' => ['required', 'array'],
            'students.*' => ['integer', 'exists:students,id'],
            'present' => ['nullable', 'array'],
            'present.*' => ['integer'],
        ], [], [
            'class_group_id' => 'class group',
        ]);

        $tenantId = $request->user()->tenant_id;
        $date = $data['date'];
        $classGroupId = (int) $data['class_group_id'];
        $studentIds = collect($data['students'])->map(fn ($id) => (int) $id);
        $presentIds = collect($request->input('present', []))->map(fn ($id) => (int) $id);

        $students = Student::whereIn('id', $studentIds)
            ->where('class_group_id', $classGroupId)
            ->when($tenantId, fn (Builder $query) => $query->where('tenant_id', $tenantId))
            ->get();

        $absentStudents = collect();

        DB::transaction(function () use ($students, $date, $presentIds, &$absentStudents) {
            foreach ($students as $student) {
                $isPresent = $presentIds->contains($student->id);

                $attendance = Attendance::withTrashed()->firstOrNew([
                    'student_id' => $student->id,
                    'date' => $date,
                ]);

                $attendance->present = $isPresent;
                $attendance->tenant_id = $student->tenant_id;

                if (method_exists($attendance, 'trashed') && $attendance->trashed()) {
                    $attendance->restore();
                }

                $attendance->save();

                if (!$isPresent) {
                    $absentStudents->push($student);
                }
            }
        });

        Attendance::whereDate('date', $date)
            ->whereNotIn('student_id', $students->pluck('id'))
            ->whereHas('student', function (Builder $query) use ($classGroupId, $tenantId) {
                $query->where('class_group_id', $classGroupId);
                if ($tenantId) {
                    $query->where('tenant_id', $tenantId);
                }
            })
            ->delete();

        $absentStudents->each(function (Student $student) use ($date) {
            $user = $student->user;

            if (!$user) {
                return;
            }

            $guardians = $user->guardians;

            if ($guardians->isEmpty()) {
                return;
            }

            $formattedDate = Carbon::parse($date)->format('d M Y');

            Notification::send($guardians, new StudentAbsentNotification($student->name, $formattedDate));
        });

        return redirect()->route('admin.attendances.entry', [
            'date' => $date,
            'class_group_id' => $classGroupId,
        ])->with('status', 'Attendance recorded.');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'date' => ['required', 'date'],
            'present' => ['required', 'boolean'],
        ]);
    }

    protected function classGroups(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        return ClassGroup::when($tenantId, fn (Builder $query) => $query->where('tenant_id', $tenantId))
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    protected function students(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        return Student::when($tenantId, fn (Builder $query) => $query->where('tenant_id', $tenantId))
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('student', function (Builder $studentQuery) use ($search) {
                $studentQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        } else {
            if (!empty($filters['from'])) {
                $query->whereDate('date', '>=', $filters['from']);
            }
            if (!empty($filters['to'])) {
                $query->whereDate('date', '<=', $filters['to']);
            }
        }

        if (!empty($filters['class_group_id'])) {
            $classGroupId = $filters['class_group_id'];
            $query->whereHas('student', fn (Builder $studentQuery) => $studentQuery->where('class_group_id', $classGroupId));
        }

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (!empty($filters['present'])) {
            $query->where('present', $filters['present'] === 'present');
        }
    }

    protected function assertTenant(Request $request, Attendance $attendance): void
    {
        $tenantId = $request->user()->tenant_id;
        if ($tenantId && $attendance->tenant_id !== $tenantId) {
            abort(403);
        }
    }
}
