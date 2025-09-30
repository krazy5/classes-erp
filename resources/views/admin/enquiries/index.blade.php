@extends('layouts.admin')

@section('title', 'Enquiries | ' . config('app.name'))
@section('header', 'Enquiries')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex items-center gap-2">
            <label for="status" class="text-sm text-gray-600 dark:text-gray-300">Status</label>
            <select id="status" name="status" onchange="this.form.submit()"
                    class="rounded border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                <option value="">All</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected($statusFilter === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('admin.enquiries.create') }}" class="inline-flex items-center rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">New Enquiry</a>
    </div>

    <div class="mt-6 overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr class="text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Class</th>
                    <th class="px-4 py-3">Source</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Assigned</th>
                    <th class="px-4 py-3">Follow Up</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($enquiries as $enquiry)
                    <tr class="text-sm text-gray-700 dark:text-gray-200">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $enquiry->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $enquiry->phone ?? $enquiry->email ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $enquiry->classGroup->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $enquiry->source ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800 dark:bg-blue-900/40 dark:text-blue-200">
                                {{ ucfirst($enquiry->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $enquiry->assignee->name ?? 'Unassigned' }}</td>
                        <td class="px-4 py-3">
                            {{ $enquiry->follow_up_at?->format('M d, Y h:i a') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.enquiries.edit', $enquiry) }}" class="rounded border border-gray-300 px-3 py-1 text-xs font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Edit</a>
                                <form action="{{ route('admin.enquiries.destroy', $enquiry) }}" method="POST" onsubmit="return confirm('Delete this enquiry?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded border border-red-300 px-3 py-1 text-xs font-medium text-red-600 transition hover:bg-red-50 dark:border-red-500 dark:text-red-400 dark:hover:bg-red-600/10">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No enquiries recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $enquiries->links() }}</div>
@endsection
