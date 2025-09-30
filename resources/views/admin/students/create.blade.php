@extends('layouts.admin')

@section('title', 'Create Student | ' . config('app.name'))
@section('header', 'Create Student')

@section('content')
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
        'route' => route('admin.students.store'),
        'method' => 'POST',
        'student' => $student,
        'classGroups' => $classGroups,
        'submitLabel' => 'Create student',
    ])
@endsection
