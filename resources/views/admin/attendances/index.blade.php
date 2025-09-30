@extends('layouts.admin')

@section('title', 'Attendance | ' . config('app.name'))
@section('header', 'Attendance Records')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Track student presence across classes and dates.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.attendances.entry', ['date' => request('date', now()->toDateString())]) }}"
               class="inline-flex items-center rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Record Attendance</a>
            <a href="{{ route('admin.attendances.create') }}"
               class="inline-flex items-center rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">New Entry</a>
        </div>
    </div>

    @if(session('status'))
        <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-600 dark:bg-green-900/40 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    <form method="GET" class="mt-6 grid gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 md:grid-cols-4">
        <div>
            <label for="search" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Search student</label>
            <input id="search" name="search" value="{{ $filters['search'] ?? '' }}"
                   class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                   placeholder="Name, email, or phone">
        </div>
        <div>
            <label for="class_group_id" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Class</label>
            <select id="class_group_id" name="class_group_id"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                <option value="">All classes</option>
                @foreach($classGroups as $id => $name)
                    <option value="{{ $id }}" @selected(($filters['class_group_id'] ?? null) == $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="date" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Specific date</label>
            <input id="date" name="date" type="date" value="{{ $filters['date'] ?? '' }}"
                   class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
        </div>
        <div>
            <label for="present" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
            <select id="present" name="present"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                <option value="">Any</option>
                <option value="present" @selected(($filters['present'] ?? null) === 'present')>Present</option>
                <option value="absent" @selected(($filters['present'] ?? null) === 'absent')>Absent</option>
            </select>
        </div>
        <div>
            <label for="from" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">From</label>
            <input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}"
                   class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
        </div>
        <div>
            <label for="to" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">To</label>
            <input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}"
                   class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
        </div>
        <div>
            <label for="student_id" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Student ID</label>
            <input id="student_id" name="student_id" type="number" value="{{ $filters['student_id'] ?? '' }}"
                   class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                   placeholder="Exact ID">
        </div>
        <div class="flex items-end gap-2">
            <button type="submit"
                    class="inline-flex flex-1 items-center justify-center rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Apply</button>
            <a href="{{ route('admin.attendances.index') }}"
               class="inline-flex items-center justify-center rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Reset</a>
        </div>
    </form>

    <div class="mt-6 grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/20 dark:text-emerald-200">
            <p class="text-xs uppercase tracking-wide">Total records</p>
            <p class="mt-2 text-2xl font-semibold">{{ number_format($summary['total']) }}</p>
        </div>
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-blue-800 dark:border-blue-900/50 dark:bg-blue-900/20 dark:text-blue-200">
            <p class="text-xs uppercase tracking-wide">Present</p>
            <p class="mt-2 text-2xl font-semibold">{{ number_format($summary['present']) }}</p>
        </div>
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800 dark:border-rose-900/50 dark:bg-rose-900/20 dark:text-rose-200">
            <p class="text-xs uppercase tracking-wide">Absent</p>
            <p class="mt-2 text-2xl font-semibold">{{ number_format($summary['absent']) }}</p>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-lg bg-white shadow dark:bg-gray-900">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-300">
                <tr>
                    <th class="px-4 py-3">Student</th>
                    <th class="px-4 py-3">Class</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Updated</th>
                    <th class="px-4 py-3 text-right"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse($attendances as $attendance)
                    <tr class="text-sm text-gray-700 dark:text-gray-200">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @php
                                    $photo = $attendance->student?->getFirstMediaUrl('photo');
                                @endphp
                                @if($photo)
                                    <img src="{{ $photo }}" alt="{{ $attendance->student?->name }}" class="h-10 w-10 rounded-full object-cover">
                                @else
                                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-xs font-semibold uppercase text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        {{ strtoupper(substr($attendance->student?->name ?? 'NA', 0, 2)) }}
                                    </span>
                                @endif
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $attendance->student?->name ?? 'Unknown student' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">ID #{{ $attendance->student_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $attendance->student?->classGroup?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $attendance->date->format('M d, Y') }}</td>
                        <td class="px-4 py-3">
                            @if($attendance->present)
                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">Present</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700 dark:bg-rose-900/40 dark:text-rose-200">Absent</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">{{ $attendance->updated_at->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.attendances.edit', $attendance) }}"
                                   class="rounded border border-gray-300 px-3 py-1 text-xs font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Edit</a>
                                <form action="{{ route('admin.attendances.destroy', $attendance) }}" method="POST"
                                      onsubmit="return confirm('Remove this attendance record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="rounded border border-red-300 px-3 py-1 text-xs font-medium text-red-600 transition hover:bg-red-50 dark:border-red-500 dark:text-red-400 dark:hover:bg-red-600/10">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No attendance records found for the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $attendances->links() }}</div>
@endsection
