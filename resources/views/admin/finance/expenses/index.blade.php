@extends('layouts.admin')

@php
    use Illuminate\Support\Str;
@endphp

@section('title', 'Expenses | ' . config('app.name'))
@section('header', 'Financial Expenses')

@section('content')
    <div class='flex flex-col gap-3 md:flex-row md:items-center md:justify-between'>
        <div>
            <p class='text-sm text-slate-500 dark:text-slate-400'>Track operational spending and keep your finances in control.</p>
            <div class='mt-2 flex flex-wrap gap-3 text-sm font-semibold text-slate-700 dark:text-slate-200'>
                <span>Total spent: <span class='text-rose-600 dark:text-rose-300'>₹{{ number_format($summary['total'], 2) }}</span></span>
                <span>Entries: {{ $summary['count'] }}</span>
            </div>
        </div>
        <a href='{{ route('admin.expenses.create') }}' class='inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700'>Record expense</a>
    </div>

    <form method='GET' class='mt-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900'>
        <div class='grid gap-4 md:grid-cols-4'>
            <div>
                <label class='text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400'>Category</label>
                <input type='text' name='category' value='{{ $filters['category'] }}' list='expense-category-suggestions' class='mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100'>
                <datalist id='expense-category-suggestions'>
                    @foreach($categorySuggestions as $suggestion)
                        <option value='{{ $suggestion }}' />
                    @endforeach
                </datalist>
            </div>
             <div>
             </div>
            <div>
                <label class='text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400'>To</label>
                <input type='date' name='to' value='{{ $filters['to'] }}' class='mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100'>
            </div>
            <div class='flex items-end gap-2'>
                <button type='submit' class='w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700'>Filter</button>
                <a href='{{ route('admin.expenses.index') }}' class='rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800'>Reset</a>
            </div>
        </div>
    </form>

    <div class='mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900'>
        <table class='min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800'>
            <thead class='bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300'>
                <tr>
                    <th class='px-4 py-3'>Title</th>
                    <th class='px-4 py-3'>Category</th>
                    <th class='px-4 py-3 text-right'>Amount</th>
                    <th class='px-4 py-3'>Incurred on</th>
                    <th class='px-4 py-3'>Payment</th>
                    <th class='px-4 py-3'>Reference</th>
                    <th class='px-4 py-3 text-right'>Actions</th>
                </tr>
            </thead>
            <tbody class='divide-y divide-slate-200 dark:divide-slate-800'>
                @forelse($expenses as $expense)
                    <tr class='text-slate-700 dark:text-slate-200'>
                        <td class='px-4 py-3'>
                            <div class='font-semibold text-slate-900 dark:text-slate-100'>{{ $expense->title }}</div>
                            <p class='text-xs text-slate-500 dark:text-slate-400'>{{ Str::limit($expense->notes, 80) }}</p>
                        </td>
                        <td class='px-4 py-3'>{{ $expense->category }}</td>
                        <td class='px-4 py-3 text-right font-semibold text-rose-600 dark:text-rose-300'>₹{{ number_format($expense->amount, 2) }}</td>
                        <td class='px-4 py-3'>{{ optional($expense->incurred_on)->format('M d, Y') }}</td>
                        <td class='px-4 py-3'>{{ $expense->payment_method ?? '--' }}</td>
                        <td class='px-4 py-3 text-xs text-slate-500 dark:text-slate-400'>{{ $expense->reference ?? '--' }}</td>
                        <td class='px-4 py-3'>
                            @if($expense->media->isEmpty())
                                <span class='text-xs text-slate-500 dark:text-slate-400'>--</span>
                            @else
                                <div class='flex flex-wrap gap-2'>
                                    @foreach($expense->media as $media)
                                        <a href='{{ route('admin.expenses.attachments.download', [$expense, $media]) }}' class='inline-flex items-center gap-1 rounded-full border border-slate-300 px-3 py-1 text-xs text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800'>
                                            @svg('heroicon-s-paper-clip', 'h-3 w-3')
                                            <span>{{ Str::limit($media->file_name, 18) }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class='px-4 py-3 text-right'>
                        <td class='px-4 py-3 text-right'>
                            <div class='flex justify-end gap-2'>
                                <a href='{{ route('admin.expenses.edit', $expense) }}' class='rounded-lg border border-slate-300 px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800'>Edit</a>
                                <form method='POST' action='{{ route('admin.expenses.destroy', $expense) }}' onsubmit="return confirm('Delete this expense entry?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type='submit' class='rounded-lg border border-rose-300 px-3 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50 dark:border-rose-500/60 dark:text-rose-300 dark:hover:bg-rose-500/10'>Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan='7' class='px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400'>No expenses recorded for the selected period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class='mt-4'>
        {{ $expenses->links() }}
    </div>
@endsection


