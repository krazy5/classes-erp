@extends('layouts.admin')

@section('title', 'Update Test Performance | ' . config('app.name'))
@section('header', 'Update Test Performance')

@section('content')
    <div class="max-w-5xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <form method="POST" action="{{ route($routePrefix.'update', $performance) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            @include('academics.test-performances._form')
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route($routePrefix.'index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Cancel</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                    @svg('heroicon-s-arrow-path', 'h-4 w-4')
                    <span>Update record</span>
                </button>
            </div>
        </form>
    </div>
@endsection
