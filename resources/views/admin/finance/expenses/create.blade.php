@extends('layouts.admin')

@section('title', 'Record Expense | ' . config('app.name'))
@section('header', 'Record Expense')

@section('content')
    <div class='max-w-4xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900'>
        <form method='POST' action='{{ route('admin.expenses.store') }}' enctype='multipart/form-data' class='space-y-6'>
            @csrf
            @include('admin.finance.expenses._form', ['expense' => $expense, 'categorySuggestions' => $categorySuggestions])
            <div class='flex items-center justify-end gap-3'>
                <a href='{{ route('admin.expenses.index') }}' class='rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800'>Cancel</a>
                <button type='submit' class='rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700'>Save expense</button>
            </div>
        </form>
    </div>
@endsection
