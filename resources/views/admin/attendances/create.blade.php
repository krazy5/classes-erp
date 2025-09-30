@extends('layouts.admin')

@section('title', 'Create Attendance | ' . config('app.name'))
@section('header', 'Log Attendance')

@section('content')
    @if ($errors->any())
        <div class="rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500 dark:bg-red-900/40 dark:text-red-200">
            <ul class="list-disc space-y-1 pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('admin.attendances._form', [
        'route' => route('admin.attendances.store'),
        'method' => 'POST',
        'attendance' => $attendance,
        'students' => $students,
        'submitLabel' => 'Save attendance',
    ])
@endsection