@extends('layouts.admin')

@section('title', 'Fee Structures | ' . config('app.name'))
@section('header', 'Fee Structures')

@section('content')
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">All Fee Structures</h2>
        <a href="{{ route('admin.fee-structures.create') }}" class="inline-flex items-center rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">New Fee Structure</a>
    </div>

    <div class="mt-6 overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr class="text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Class</th>
                    <th class="px-4 py-3">Subject</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3">Frequency</th>
                    <th class="px-4 py-3">Active</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($feeStructures as $structure)
                    <tr class="text-sm text-gray-700 dark:text-gray-200">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $structure->name }}</div>
                            @if($structure->effective_from || $structure->effective_to)
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $structure->effective_from?->format('M d, Y') ?? 'Start' }}
                                    —
                                    {{ $structure->effective_to?->format('M d, Y') ?? 'Open' }}
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $structure->classGroup->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $structure->subject->name ?? '—' }}</td>
                        <td class="px-4 py-3">? {{ number_format($structure->amount, 2) }}</td>
                        <td class="px-4 py-3 text-xs uppercase">{{ str_replace('_', ' ', $structure->frequency) }}</td>
                        <td class="px-4 py-3">
                            @if($structure->is_active)
                                <span class="inline-flex items-center rounded bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">Active</span>
                            @else
                                <span class="inline-flex items-center rounded bg-gray-200 px-2 py-1 text-xs font-semibold text-gray-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.fee-structures.edit', $structure) }}" class="rounded border border-gray-300 px-3 py-1 text-xs font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Edit</a>
                                <form action="{{ route('admin.fee-structures.destroy', $structure) }}" method="POST" onsubmit="return confirm('Delete this fee structure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded border border-red-300 px-3 py-1 text-xs font-medium text-red-600 transition hover:bg-red-50 dark:border-red-500 dark:text-red-400 dark:hover:bg-red-600/10">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No fee structures defined yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $feeStructures->links() }}</div>
@endsection
