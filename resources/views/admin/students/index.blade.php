@extends('layouts.admin')

@section('title', 'Students | ' . config('app.name'))
@section('header', 'Students')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <form method="GET" class="w-full md:max-w-sm">
            <label class="sr-only" for="search">Search students</label>
            <div class="flex rounded-lg shadow-sm">
                <input id="search" name="search" value="{{ $search }}"
                       class="w-full rounded-l-lg border border-r-0 border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                       placeholder="Search by name, email, or phone">
                <button type="submit"
                        class="rounded-r-lg bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-700">Search</button>
            </div>
        </form>

        <a href="{{ route('admin.students.create') }}"
           class="inline-flex items-center justify-center rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">New Student</a>
    </div>
    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 sm:hidden">Swipe sideways to see the full student details and actions.</p>

    @if(session('status'))
        <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-600 dark:bg-green-900/40 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    <div class="mt-6 rounded-lg bg-white shadow dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="min-w-[720px] w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-300">
                <tr>
                    <th class="px-4 py-3">Student</th>
                    <th class="px-4 py-3">Class</th>
                    <th class="px-4 py-3">Contact</th>
                    <th class="px-4 py-3">DOB</th>
                    <th class="px-4 py-3 text-right"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse($students as $student)
                    <tr class="text-sm text-gray-700 dark:text-gray-200">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @php
                                    $photo = $student->getFirstMediaUrl('photo');
                                @endphp
                                @if($photo)
                                    <img src="{{ $photo }}" alt="{{ $student->name }}" class="h-10 w-10 rounded-full object-cover">
                                @else
                                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-xs font-semibold uppercase text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        {{ strtoupper(substr($student->name, 0, 2)) }}
                                    </span>
                                @endif
                                <div>
                                    <a href="{{ route('admin.students.show', $student) }}"
                                       class="font-semibold text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-300">
                                        {{ $student->name }}
                                    </a>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        @if($student->gender)
                                            <span class="uppercase">{{ $student->gender }}</span>
                                        @endif
                                        @if($student->user_id)
                                            <span class="ml-2 text-xs text-indigo-500">User #{{ $student->user_id }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            {{ $student->classGroup->name ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3">
                            <div>{{ $student->email ?? '—' }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $student->phone ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            {{ optional($student->dob)->format('M d, Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.students.edit', $student) }}"
                                   class="rounded border border-gray-300 px-3 py-1 text-xs font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Edit</a>
                                <form action="{{ route('admin.students.destroy', $student) }}" method="POST"
                                      onsubmit="return confirm('Delete this student?');">
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
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                            No students recorded yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $students->links() }}
    </div>
@endsection
