<?php

namespace App\Http\Controllers;

use App\Models\ClassGroup;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TestPerformance;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TestPerformanceController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', TestPerformance::class);

        $user = $request->user();
        $tenantId = $user->tenant_id;

        $filters = [
            'class_group_id' => $request->input('class_group_id'),
            'subject_id' => $request->input('subject_id'),
            'student_id' => $request->input('student_id'),
            'term' => $request->string('term')->trim()->value(),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'search' => $request->string('search')->trim()->value(),
        ];

        $query = TestPerformance::query()
            ->with(['student', 'classGroup', 'subject', 'recordedBy'])
            ->when($tenantId, fn (Builder $builder) => $builder->where('tenant_id', $tenantId));

        $this->scopeIndexQueryForRole($query, $user);

        if ($filters['class_group_id']) {
            $query->where('class_group_id', $filters['class_group_id']);
        }

        if ($filters['subject_id']) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if ($filters['student_id']) {
            $query->where('student_id', $filters['student_id']);
        }

        if ($filters['term']) {
            $query->where('term', 'like', '%' . $filters['term'] . '%');
        }

        if ($filters['from']) {
            $query->whereDate('test_date', '>=', Carbon::parse($filters['from']));
        }

        if ($filters['to']) {
            $query->whereDate('test_date', '<=', Carbon::parse($filters['to']));
        }

        if ($filters['search']) {
            $query->where(function (Builder $builder) use ($filters) {
                $builder->where('title', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('remarks', 'like', '%' . $filters['search'] . '%')
                    ->orWhereHas('student', function (Builder $studentQuery) use ($filters) {
                        $studentQuery->where('name', 'like', '%' . $filters['search'] . '%');
                    });
            });
        }

        $performances = $query
            ->orderByDesc('test_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $related = $this->resolveRelatedOptions($user, $tenantId, $filters['class_group_id']);

        return view($this->resolveView('index', $user), [
            'performances' => $performances,
            'filters' => $filters,
            'students' => $related['students'],
            'subjects' => $related['subjects'],
            'classGroups' => $related['classGroups'],
            'canManage' => $user->can('create', TestPerformance::class),
            'canDelete' => $user->hasAnyRole(['admin', 'manager', 'teacher']),
            'routePrefix' => $this->routePrefix($user),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', TestPerformance::class);

        $user = $request->user();
        $tenantId = $user->tenant_id;

        $related = $this->resolveRelatedOptions($user, $tenantId);

        return view($this->resolveView('create', $user), [
            'performance' => new TestPerformance([
                'test_date' => now()->toDateString(),
            ]),
            'students' => $related['students'],
            'subjects' => $related['subjects'],
            'classGroups' => $related['classGroups'],
            'routePrefix' => $this->routePrefix($user),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', TestPerformance::class);

        $data = $this->validated($request);

        /** @var TestPerformance $performance */
        $performance = TestPerformance::create(array_merge($data, [
            'tenant_id' => $request->user()->tenant_id,
            'recorded_by' => $request->user()->id,
        ]));

        $this->syncAttachments($performance, $request->file('attachments', []));

        return redirect()->route($this->routeName('index', $request->user()))
            ->with('status', 'Test performance recorded successfully.');
    }

    public function edit(Request $request, TestPerformance $testPerformance): View
    {
        Gate::authorize('update', $testPerformance);

        $user = $request->user();
        $tenantId = $user->tenant_id;

        $related = $this->resolveRelatedOptions($user, $tenantId, $testPerformance->class_group_id);

        return view($this->resolveView('edit', $user), [
            'performance' => $testPerformance->loadMissing(['student', 'subject', 'classGroup', 'media']),
            'students' => $related['students'],
            'subjects' => $related['subjects'],
            'classGroups' => $related['classGroups'],
            'routePrefix' => $this->routePrefix($user),
        ]);
    }

    public function update(Request $request, TestPerformance $testPerformance): RedirectResponse
    {
        Gate::authorize('update', $testPerformance);

        $data = $this->validated($request);

        $testPerformance->fill(array_merge($data, [
            'recorded_by' => $request->user()->id,
        ]));

        $testPerformance->save();

        $this->syncAttachments($testPerformance, $request->file('attachments', []), $request->input('remove_attachments', []));

        return redirect()->route($this->routeName('index', $request->user()))
            ->with('status', 'Test performance updated successfully.');
    }

    public function destroy(Request $request, TestPerformance $testPerformance): RedirectResponse
    {
        Gate::authorize('delete', $testPerformance);

        $testPerformance->delete();

        return redirect()->route($this->routeName('index', $request->user()))
            ->with('status', 'Test performance removed.');
    }

    public function download(Request $request, TestPerformance $testPerformance, Media $media)
    {
        Gate::authorize('download', $testPerformance);

        abort_unless($media->model_id === $testPerformance->id && $media->model_type === TestPerformance::class, 404);

        return $media->toInlineResponse($request);
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'class_group_id' => ['nullable', 'exists:class_groups,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:150'],
            'assessment_type' => ['nullable', 'string', 'max:60'],
            'term' => ['nullable', 'string', 'max:60'],
            'test_date' => ['nullable', 'date'],
            'max_score' => ['nullable', 'numeric', 'min:0'],
            'score' => ['nullable', 'numeric', 'min:0'],
            'grade' => ['nullable', 'string', 'max:12'],
            'remarks' => ['nullable', 'string'],
            'metadata.weightage' => ['nullable', 'numeric', 'min:0'],
        ]);

        $metadata = Arr::only($data['metadata'] ?? [], ['weightage']);
        $data['metadata'] = $metadata ?: null;

        return $data;
    }

    protected function syncAttachments(TestPerformance $performance, array $newFiles = [], array $removals = []): void
    {
        if (!empty($removals)) {
            $performance->media()
                ->whereIn('id', $removals)
                ->get()
                ->each->delete();
        }

        collect($newFiles)
            ->filter(fn ($file) => $file instanceof UploadedFile)
            ->each(fn (UploadedFile $file) => $performance->addMedia($file)->toMediaCollection('attachments'));
    }

    protected function resolveRelatedOptions($user, ?int $tenantId, ?int $classGroupFilter = null): array
    {
        $studentsQuery = Student::query()
            ->orderBy('name');

        if ($tenantId) {
            $studentsQuery->where('tenant_id', $tenantId);
        }

        if ($user->hasRole('student')) {
            $studentsQuery->whereHas('user', fn (Builder $builder) => $builder->where('users.id', $user->id));
        } elseif ($user->hasRole('parent')) {
            $studentsQuery->whereIn('id', $user->wards()->pluck('students.id'));
        } elseif ($classGroupFilter) {
            $studentsQuery->where('class_group_id', $classGroupFilter);
        }

        $students = $studentsQuery->pluck('name', 'id');

        $subjects = Subject::query()
            ->when($tenantId, fn (Builder $builder) => $builder->where('tenant_id', $tenantId))
            ->orderBy('name')
            ->pluck('name', 'id');

        $classGroups = ClassGroup::query()
            ->when($tenantId, fn (Builder $builder) => $builder->where('tenant_id', $tenantId))
            ->orderBy('name')
            ->pluck('name', 'id');

        return compact('students', 'subjects', 'classGroups');
    }

    protected function resolveView(string $view, $user): string
    {
        return match (true) {
            $user->hasAnyRole(['admin', 'manager', 'reception']) => "admin.academics.test-performances.$view",
            $user->hasRole('teacher') => "teacher.test-performances.$view",
            $user->hasRole('parent') => "parent.test-performances.$view",
            $user->hasRole('student') => "student.test-performances.$view",
            default => "admin.academics.test-performances.$view",
        };
    }

    protected function routePrefix($user): string
    {
        return match (true) {
            $user->hasAnyRole(['admin', 'manager', 'reception']) => 'academics.test-performances.',
            $user->hasRole('teacher') => 'teacher.test-performances.',
            $user->hasRole('parent') => 'parent.test-performances.',
            $user->hasRole('student') => 'student.test-performances.',
            default => 'academics.test-performances.',
        };
    }

    protected function routeName(string $name, $user): string
    {
        return match (true) {
            $user->hasAnyRole(['admin', 'manager', 'reception']) => "academics.test-performances.$name",
            $user->hasRole('teacher') => "teacher.test-performances.$name",
            $user->hasRole('parent') => "parent.test-performances.$name",
            $user->hasRole('student') => "student.test-performances.$name",
            default => "academics.test-performances.$name",
        };
    }

    protected function scopeIndexQueryForRole(Builder $query, $user): void
    {
        if ($user->hasRole('student')) {
            $query->whereHas('student', fn (Builder $builder) => $builder->where('user_id', $user->id));
        }

        if ($user->hasRole('parent')) {
            $studentIds = $user->wards()->pluck('students.id');
            $query->whereIn('student_id', $studentIds);
        }
    }
}

