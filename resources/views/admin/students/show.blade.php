@extends('layouts.admin')

@section('title', $student->name . ' | ' . config('app.name'))
@section('header', 'Student Profile')

@section('content')
    <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-900">
        <div class="flex flex-col gap-6 p-6 md:flex-row md:items-center">
            @php
                $photo = $student->getFirstMediaUrl('photo');
            @endphp
            @if($photo)
                <img src="{{ $photo }}" alt="{{ $student->name }}" class="h-28 w-28 rounded-full object-cover">
            @else
                <span class="flex h-28 w-28 items-center justify-center rounded-full bg-gray-200 text-lg font-bold uppercase text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    {{ strtoupper(substr($student->name, 0, 2)) }}
                </span>
            @endif

            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $student->name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $student->classGroup->name ?? 'No class assigned' }}
                </p>
                <div class="mt-3 flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-300">
                    <span>Email: <strong class="font-medium text-gray-800 dark:text-gray-100">{{ $student->email ?? '—' }}</strong></span>
                    <span>Phone: <strong class="font-medium text-gray-800 dark:text-gray-100">{{ $student->phone ?? '—' }}</strong></span>
                    <span>DOB: <strong class="font-medium text-gray-800 dark:text-gray-100">{{ optional($student->dob)->format('M d, Y') ?? '—' }}</strong></span>
                    <span>Gender: <strong class="font-medium text-gray-800 dark:text-gray-100">{{ $student->gender ?? '—' }}</strong></span>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-800 px-6 py-5 text-sm text-gray-700 dark:text-gray-300">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Address</h3>
            <p class="mt-2 whitespace-pre-line">{{ $student->address ?? 'No address on file.' }}</p>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-800 px-6 py-5">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Guardians</h3>
                <a href="{{ route('admin.students.add-guardian', $student) }}"
                   class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Add guardian</a>
            </div>

            @php
                $guardians = optional($student->user)->guardians ?? collect();
            @endphp

            @if($guardians->isEmpty())
                <p class="text-sm text-gray-600 dark:text-gray-400">No guardians linked yet.</p>
            @else
                <ul class="space-y-4">
                    @foreach($guardians as $guardian)
                        <li class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $guardian->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $guardian->email }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Relationship: {{ $guardian->pivot->relationship_type ?: 'Not specified' }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
                                        Parent portal
                                    </span>
                                    <a href="{{ route('admin.students.guardians.edit', [$student, $guardian]) }}"
                                       class="rounded border border-gray-300 px-3 py-1 text-xs font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                                        Edit guardian
                                    </a>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="flex flex-wrap justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-800">
            <a href="{{ route('admin.students.payments', $student) }}"
               class="rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">View payments</a>
            <a href="{{ route('admin.students.documents', $student) }}"
               class="rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Manage documents</a>
            <a href="{{ route('admin.students.edit', $student) }}"
               class="rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Edit</a>
            <form action="{{ route('admin.students.destroy', $student) }}" method="POST"
                  onsubmit="return confirm('Delete this student?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="rounded border border-red-300 px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-500 dark:text-red-400 dark:hover:bg-red-600/10">Delete</button>
            </form>
        </div>
    </div>
@endsection
