<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Feedback;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $status = $request->string('status')->trim();
        $guardianName = $request->string('guardian_name')->trim();
        $guardianEmail = $request->string('guardian_email')->trim();
        $studentName = $request->string('student_name')->trim();
        $studentEmail = $request->string('student_email')->trim();
        $classGroupInput = $request->query('class_group_id');
        $classGroupId = is_numeric($classGroupInput) ? (int) $classGroupInput : 0;

        $statusValue = $status->value();
        $guardianNameValue = $guardianName->value();
        $guardianEmailValue = $guardianEmail->value();
        $studentNameValue = $studentName->value();
        $studentEmailValue = $studentEmail->value();

        $feedback = Feedback::query()
            ->with([
                'author' => fn ($query) => $query->with([
                    'students.studentProfile.classGroup',
                ]),
                'responder',
            ])
            ->when($tenantId, fn (Builder $builder) => $builder->where('tenant_id', $tenantId))
            ->when($status->isNotEmpty(), fn (Builder $builder) => $builder->where('status', $statusValue))
            ->when($guardianName->isNotEmpty(), fn (Builder $builder) => $builder->whereHas('author', function (Builder $authorQuery) use ($guardianNameValue) {
                $authorQuery->where('name', 'like', "%{$guardianNameValue}%");
            }))
            ->when($guardianEmail->isNotEmpty(), fn (Builder $builder) => $builder->whereHas('author', function (Builder $authorQuery) use ($guardianEmailValue) {
                $authorQuery->where('email', 'like', "%{$guardianEmailValue}%");
            }))
            ->when($studentName->isNotEmpty(), fn (Builder $builder) => $builder->whereHas('author.students', function (Builder $studentQuery) use ($studentNameValue) {
                $studentQuery->where('name', 'like', "%{$studentNameValue}%")
                    ->orWhereHas('studentProfile', function (Builder $profileQuery) use ($studentNameValue) {
                        $profileQuery->where('name', 'like', "%{$studentNameValue}%");
                    });
            }))
            ->when($studentEmail->isNotEmpty(), fn (Builder $builder) => $builder->whereHas('author.students', function (Builder $studentQuery) use ($studentEmailValue) {
                $studentQuery->where('email', 'like', "%{$studentEmailValue}%")
                    ->orWhereHas('studentProfile', function (Builder $profileQuery) use ($studentEmailValue) {
                        $profileQuery->where('email', 'like', "%{$studentEmailValue}%");
                    });
            }))
            ->when($classGroupId > 0, fn (Builder $builder) => $builder->whereHas('author.students.studentProfile', function (Builder $profileQuery) use ($classGroupId) {
                $profileQuery->where('class_group_id', $classGroupId);
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $classGroups = ClassGroup::query()
            ->select(['id', 'name', 'tenant_id'])
            ->when($tenantId, fn (Builder $builder) => $builder->where('tenant_id', $tenantId))
            ->orderBy('name')
            ->get();

        return view('admin.feedback.index', [
            'feedback' => $feedback,
            'classGroups' => $classGroups,
            'filters' => [
                'status' => $statusValue,
                'guardian_name' => $guardianNameValue,
                'guardian_email' => $guardianEmailValue,
                'student_name' => $studentNameValue,
                'student_email' => $studentEmailValue,
                'class_group_id' => $classGroupId,
            ],
        ]);
    }

    public function update(Request $request, Feedback $feedback): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;

        if ($tenantId && $feedback->tenant_id !== $tenantId) {
            abort(403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved'],
            'response' => ['nullable', 'string'],
        ]);

        $feedback->fill([
            'status' => $data['status'],
            'response' => $data['response'] ?? null,
            'responded_by' => $request->user()->id,
            'responded_at' => now(),
        ])->save();

        return redirect()->route('admin.feedback.index')
            ->with('status', 'Feedback updated.');
    }

    public function destroy(Request $request, Feedback $feedback): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;

        if ($tenantId && $feedback->tenant_id !== $tenantId) {
            abort(403);
        }

        $feedback->delete();

        return redirect()->route('admin.feedback.index')
            ->with('status', 'Feedback removed.');
    }
}
