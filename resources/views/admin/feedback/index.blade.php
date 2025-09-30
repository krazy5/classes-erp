@extends('layouts.admin')

@section('title', 'Feedback | ' . config('app.name'))
@section('header', 'Parent Feedback')

@section('content')
    @if(session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <form method="GET" class="grid gap-3 md:grid-cols-3">
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</label>
                <select name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    <option value="">All</option>
                    @foreach(['open' => 'Open', 'in_progress' => 'In progress', 'resolved' => 'Resolved'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2 flex items-end justify-end">
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Filter</button>
            </div>
        </form>
    </div>

    <div class="mt-6 space-y-4">
        @forelse($feedback as $item)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $item->subject }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Submitted {{ $item->created_at->format('d M Y H:i') }} by {{ optional($item->author)->name ?? 'Unknown' }} @if($item->category) • {{ $item->category }} @endif</p>
                    </div>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold @class([
                        'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-200' => $item->status === 'open',
                        'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200' => $item->status === 'in_progress',
                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200' => $item->status === 'resolved',
                    ])">{{ \Illuminate\Support\Str::headline($item->status) }}</span>
                </div>

                <p class="mt-3 whitespace-pre-line text-sm text-slate-700 dark:text-slate-200">{{ $item->message }}</p>

                @if($item->response)
                    <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
                        <p class="font-semibold">Response from {{ optional($item->responder)->name ?? 'Team' }} ({{ optional($item->responded_at)->format('d M Y H:i') ?? 'N/A' }})</p>
                        <p class="mt-2 whitespace-pre-line">{{ $item->response }}</p>
                    </div>
                @endif

                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <form method="POST" action="{{ route('admin.feedback.update', $item) }}" class="flex flex-1 flex-col gap-2 md:flex-row md:items-end md:gap-3">
                        @csrf
                        @method('PUT')
                        <div class="flex-1">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</label>
                            <select name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                                @foreach(['open' => 'Open', 'in_progress' => 'In progress', 'resolved' => 'Resolved'] as $value => $label)
                                    <option value="{{ $value }}" @selected($item->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Response</label>
                            <textarea name="response" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">{{ old('response', $item->response) }}</textarea>
                        </div>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Update</button>
                    </form>
                    <form method="POST" action="{{ route('admin.feedback.destroy', $item) }}" onsubmit="return confirm('Delete this feedback entry?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg border border-rose-300 px-3 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/40 dark:text-rose-200 dark:hover:bg-rose-500/10">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-6 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">No feedback submitted yet.</div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $feedback->links() }}
    </div>
@endsection
