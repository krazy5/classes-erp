@extends('layouts.admin')

@section('title', 'Announcements | ' . config('app.name'))
@section('header', 'Announcements')

@section('content')
    @if(session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <form method="GET" class="flex flex-1 flex-wrap gap-3">
            <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search title"
                   class="flex-1 min-w-[200px] rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
            <select name="class_group_id" class="min-w-[200px] rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                <option value="">All classes</option>
                @foreach($classGroups as $id => $name)
                    <option value="{{ $id }}" @selected($filters['class_group_id'] == $id)>{{ $name }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Filter</button>
        </form>

        <a href="{{ route('admin.announcements.create') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">New announcement</a>
    </div>

    <div class="mt-6 space-y-4">
        @forelse($announcements as $announcement)
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $announcement->title }}</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Published {{ optional($announcement->published_at)->format('d M Y H:i') ?? $announcement->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Delete this announcement?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg border border-rose-300 px-3 py-1 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/40 dark:text-rose-200 dark:hover:bg-rose-500/10">Delete</button>
                    </form>
                </div>
                <p class="mt-3 whitespace-pre-line text-sm text-slate-700 dark:text-slate-200">{{ $announcement->body }}</p>
                <div class="mt-3 flex flex-wrap gap-2 text-xs text-slate-500 dark:text-slate-400">
                    @if($announcement->classGroups->isNotEmpty())
                        <span>Classes: {{ $announcement->classGroups->pluck('name')->implode(', ') }}</span>
                    @endif
                    @if($announcement->students->isNotEmpty())
                        <span>Students: {{ $announcement->students->pluck('name')->implode(', ') }}</span>
                    @endif
                    @if($announcement->classGroups->isEmpty() && $announcement->students->isEmpty())
                        <span>Audience: Entire institute</span>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">No announcements yet.</div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $announcements->links() }}
    </div>
@endsection
