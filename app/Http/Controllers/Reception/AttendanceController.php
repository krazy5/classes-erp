<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassGroup;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->can('attendance.record.class'), 403);

        $data = $request->validate([
            'class_group_id' => ['required', 'integer', Rule::exists('class_groups', 'id')],
            'date' => ['required', 'date'],
            'present' => ['nullable', 'array'],
            'present.*' => ['integer'],
        ], [], [
            'class_group_id' => 'class group',
        ]);

        $classGroupId = (int) $data['class_group_id'];
        $tenantId = $user->tenant_id;

        ClassGroup::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->findOrFail($classGroupId);

        $students = Student::query()
            ->where('class_group_id', $classGroupId)
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderBy('name')
            ->get();

        if ($students->isEmpty()) {
            return redirect()->route('reception.dashboard', [
                'class_group_id' => $classGroupId,
                'date' => $data['date'],
            ])->with('status', 'No students found for the selected class.');
        }

        $presentIds = collect($data['present'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter();

        DB::transaction(function () use ($students, $data, $presentIds) {
            foreach ($students as $student) {
                $isPresent = $presentIds->contains($student->id);

                $attendance = Attendance::withTrashed()->firstOrNew([
                    'student_id' => $student->id,
                    'date' => $data['date'],
                ]);

                $attendance->present = $isPresent;
                $attendance->tenant_id = $student->tenant_id;

                if (method_exists($attendance, 'trashed') && $attendance->trashed()) {
                    $attendance->restore();
                }

                $attendance->save();
            }
        });

        return redirect()->route('reception.dashboard', [
            'class_group_id' => $classGroupId,
            'date' => $data['date'],
        ])->with('status', 'Attendance saved successfully.');
    }
}
