@extends('layouts.admin')

@section('title', 'Edit Teacher | ' . config('app.name'))
@section('header', 'Edit Teacher')

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

    @include('management.teachers._form', [
        'route' => route('management.teachers.update', $teacher),
        'method' => 'PUT',
        'teacher' => $teacher,
        'submitLabel' => 'Update teacher',
    ])
@endsection
