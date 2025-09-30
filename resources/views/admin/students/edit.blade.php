@extends('layouts.admin')

@section('title', 'Edit Student | ' . config('app.name'))
@section('header', 'Edit Student')

@section('content')
    {{-- Shortcuts toolbar --}}
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.students.add-guardian', $student) }}"
           class="inline-flex items-center rounded-lg border px-3 py-1.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">
            Edit Guardian
        </a>

        <a href="{{ route('admin.students.add-fee-plan', $student) }}"
           class="inline-flex items-center rounded-lg border px-3 py-1.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">
            Edit Fee Plan
        </a>

        <a href="{{ route('admin.payments.index', ['student_id' => $student->id]) }}"
           class="inline-flex items-center rounded-lg border px-3 py-1.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">
            Payments
        </a>
        <a href="{{ route('admin.students.documents', $student) }}"
           class="inline-flex items-center rounded-lg border px-3 py-1.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">
            Manage Documents
        </a>


        <a href="{{ route('admin.students.show', $student) }}"
           class="inline-flex items-center rounded-lg border px-3 py-1.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">
            View Profile
        </a>
    </div>

    @if(session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-600 dark:bg-green-900/40 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500 dark:bg-red-900/40 dark:text-red-200">
            <ul class="list-disc space-y-1 pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('admin.students._form', [
        'route' => route('admin.students.update', $student),
        'method' => 'PUT',
        'student' => $student,
        'classGroups' => $classGroups,
        'submitLabel' => 'Update student',
    ])
@endsection
