@php
    use Illuminate\Support\Str;
@endphp

@if (session('status'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-900/30 dark:text-emerald-200">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/40 dark:bg-rose-900/30 dark:text-rose-200">
        {{ $errors->first() }}
    </div>
@endif

<div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Student Test Performance</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">Centralise internal test observations, upload evaluated answer sheets, and track progress over time.</p>
    </div>
    @if ($canManage)
        <a href="{{ route($routePrefix.'create') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
            @svg('heroicon-s-plus', 'h-4 w-4')
            <span>Record performance</span>
        </a>
    @endif
</div>

<form method="GET" class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
        <div>
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Class / Batch</label>
            <select name="class_group_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                <option value="">All</option>
                @foreach ($classGroups as $id => $name)
                    <option value="{{ $id }}" @selected((int) ($filters['class_group_id'] ?? null) === (int) $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Student</label>
            <select name="student_id" data-behavior="student-search" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                <option value="">All</option>
                @foreach ($students as $id => $name)
                    <option value="{{ $id }}" @selected((int) ($filters['student_id'] ?? null) === (int) $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Subject</label>
            <select name="subject_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                <option value="">All</option>
                @foreach ($subjects as $id => $name)
                    <option value="{{ $id }}" @selected((int) ($filters['subject_id'] ?? null) === (int) $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Term</label>
            <input type="text" name="term" value="{{ $filters['term'] ?? '' }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" placeholder="e.g. Term 1">
        </div>
        <div>
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">From</label>
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
        </div>
        <div>
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">To</label>
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
        </div>
        <div class="xl:col-span-2">
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Search</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search by assessment or remarks" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
        </div>
        <div class="flex items-end gap-3">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                @svg('heroicon-s-funnel', 'h-4 w-4')
                <span>Apply</span>
            </button>
            <a href="{{ route($routePrefix.'index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Reset</a>
        </div>
    </div>
</form>

<div class="mt-6 rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-800">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Performance log</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $performances->total() }} record{{ $performances->total() === 1 ? '' : 's' }} found</p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                <tr>
                    <th class="px-4 py-3 text-left">Student</th>
                    <th class="px-4 py-3 text-left">Class</th>
                    <th class="px-4 py-3 text-left">Assessment</th>
                    <th class="px-4 py-3 text-right">Score</th>
                    <th class="px-4 py-3 text-left">Grade</th>
                    <th class="px-4 py-3 text-left">Recorded</th>
                    <th class="px-4 py-3 text-left">Attachments</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse ($performances as $performance)
                    @php
                        $scoreDisplay = $performance->max_score ? number_format((float) $performance->score, 2).' / '.number_format((float) $performance->max_score, 2) : number_format((float) $performance->score, 2);
                        $percentage = $performance->percentage !== null ? number_format((float) $performance->percentage, 1).'%' : '—';
                        $sharePayload = trim("Student: {$performance->student?->name}\nClass: ".($performance->classGroup?->name ?? '—')."\nAssessment: {$performance->title}\nType: ".($performance->assessment_type ?? 'General')."\nScore: {$scoreDisplay} ({$percentage})\nGrade: ".($performance->grade ?? '—')."\nDate: ".optional($performance->test_date)->format('d M Y'));
                    @endphp
                    <tr class="text-slate-700 dark:text-slate-200">
                        <td class="px-4 py-3 font-semibold text-slate-900 dark:text-slate-100">
                            {{ $performance->student?->name ?? 'Unknown' }}
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $performance->student?->email }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $performance->classGroup?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-800 dark:text-slate-100">{{ $performance->title }}</div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $performance->assessment_type ?? 'Assessment' }} • {{ optional($performance->test_date)->format('d M Y') ?? 'No date' }}
                            </p>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-semibold text-indigo-600 dark:text-indigo-300">{{ $scoreDisplay }}</span>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $percentage }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200">{{ $performance->grade ?? 'Pending' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div>{{ optional($performance->created_at)->format('d M Y') }}</div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $performance->recordedBy?->name ?? 'System' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @if ($performance->media->isEmpty())
                                <span class="text-xs text-slate-500 dark:text-slate-400">—</span>
                            @else
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($performance->media as $media)
                                        <a href="{{ route($routePrefix.'attachments.download', [$performance, $media]) }}" class="inline-flex items-center gap-1 rounded-full border border-slate-300 px-3 py-1 text-xs text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                            @svg('heroicon-s-paper-clip', 'h-3 w-3')
                                            <span>{{ Str::limit($media->file_name, 20) }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 px-2 py-1 text-xs font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" data-share="{{ $sharePayload }}" data-share-title="Test Performance">
                                    @svg('heroicon-s-share', 'h-3 w-3')
                                    <span>Share</span>
                                </button>
                                @if ($canManage)
                                    <a href="{{ route($routePrefix.'edit', $performance) }}" class="rounded-lg border border-indigo-200 px-2 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-50 dark:border-indigo-500/40 dark:text-indigo-300 dark:hover:bg-indigo-500/10">Edit</a>
                                @endif
                                @if ($canDelete)
                                    <form method="POST" action="{{ route($routePrefix.'destroy', $performance) }}" onsubmit="return confirm('Delete this record?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-rose-300 px-2 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50 dark:border-rose-500/60 dark:text-rose-300 dark:hover:bg-rose-500/10">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">No test performances recorded for the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">
        {{ $performances->links() }}
    </div>
</div>
