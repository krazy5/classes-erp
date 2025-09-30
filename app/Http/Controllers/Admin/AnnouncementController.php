<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\ClassGroup;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $search = $request->string('search')->trim();
        $classGroupId = $request->input('class_group_id');

        $query = Announcement::query()
            ->with('classGroups', 'students')
            ->when($tenantId, fn ($builder) => $builder->where('tenant_id', $tenantId))
            ->when($search->isNotEmpty(), fn ($builder) => $builder->where('title', 'like', "%{$search}%"))
            ->when($classGroupId, fn ($builder) => $builder->whereHas('classGroups', fn ($relation) => $relation->where('class_group_id', $classGroupId)))
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');

        /** @var LengthAwarePaginator $announcements */
        $announcements = $query->paginate(10)->withQueryString();

        $classGroups = ClassGroup::query()
            ->when($tenantId, fn ($builder) => $builder->where('tenant_id', $tenantId))
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('admin.announcements.index', [
            'announcements' => $announcements,
            'classGroups' => $classGroups,
            'filters' => [
                'search' => $search->value(),
                'class_group_id' => $classGroupId,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        $classGroups = ClassGroup::query()
            ->when($tenantId, fn ($builder) => $builder->where('tenant_id', $tenantId))
            ->orderBy('name')
            ->get(['id', 'name']);

        $students = Student::query()
            ->when($tenantId, fn ($builder) => $builder->where('tenant_id', $tenantId))
            ->with(['user.guardians'])
            ->orderBy('name')
            ->get()
            ->map(function (Student $student) {
                $accountName = optional($student->user)->name;
                $guardianNames = optional($student->user)->guardians?->pluck('name')->filter()->implode(', ');

                $pieces = array_filter([$student->name, $accountName, $guardianNames]);
                $student->search_label = mb_strtolower(implode(' ', $pieces));
                $student->display_label = trim($student->name . ($guardianNames ? ' (Guardians: ' . $guardianNames . ')' : ''));

                return $student;
            });

        return view('admin.announcements.create', [
            'classGroups' => $classGroups,
            'students' => $students,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'publish_now' => ['sometimes', 'boolean'],
            'scheduled_for' => ['nullable', 'date'],
            'class_group_ids' => ['nullable', 'array'],
            'class_group_ids.*' => ['integer', 'exists:class_groups,id'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer', 'exists:students,id'],
            'send_to_all' => ['sometimes', 'boolean'],
        ]);

        $announcement = Announcement::create([
            'tenant_id' => $user->tenant_id,
            'title' => $data['title'],
            'body' => $data['body'],
            'published_at' => $this->resolvePublishDate($data),
        ]);

        if (empty($data['send_to_all'])) {
            $classGroups = collect($data['class_group_ids'] ?? [])->filter()->all();
            $students = collect($data['student_ids'] ?? [])->filter()->all();

            $announcement->classGroups()->sync($classGroups);
            $announcement->students()->sync($students);
        } else {
            $announcement->classGroups()->sync([]);
            $announcement->students()->sync([]);
        }

        return redirect()->route('admin.announcements.index')
            ->with('status', 'Announcement published successfully.');
    }

    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;

        if ($tenantId && $announcement->tenant_id !== $tenantId) {
            abort(403);
        }

        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('status', 'Announcement deleted.');
    }

    protected function resolvePublishDate(array $data)
    {
        if (!empty($data['publish_now'])) {
            return Carbon::now();
        }

        if (!empty($data['scheduled_for'])) {
            return Carbon::parse($data['scheduled_for']);
        }

        return Carbon::now();
    }
}
