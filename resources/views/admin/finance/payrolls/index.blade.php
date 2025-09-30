@extends('layouts.admin')

@php
    use Illuminate\Support\Str;
@endphp

@section('title', 'Payroll | ' . config('app.name'))
@section('header', 'Staff Payroll')

@section('content')
    <div class='flex flex-col gap-3 md:flex-row md:items-center md:justify-between'>
        <div>
            <p class='text-sm text-slate-500 dark:text-slate-400'>Monitor salaries and payouts for teachers and staff.</p>
            <div class='mt-2 flex flex-wrap gap-3 text-sm font-semibold text-slate-700 dark:text-slate-200'>
                <span>Total scheduled: <span class='text-emerald-600 dark:text-emerald-300'>₹{{ number_format($summary['total'], 2) }}</span></span>
                <span>Entries: {{ $summary['count'] }}</span>
            </div>
        </div>
        <a href='{{ route('admin.payrolls.create') }}' class='inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700'>New payroll</a>
    </div>

    <form method='GET' class='mt-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900'>
        <div class='grid gap-4 md:grid-cols-4'>
            <div>
                <label class='text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400'>Status</label>
                <select name='status' class='mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100'>
                    <option value=''>All</option>
                    @foreach(['pending' => 'Pending', 'processing' => 'Processing', 'paid' => 'Paid'] as $value => $label)
                        <option value='{{ $value }}' @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class='text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400'>From</label>
                <input type='date' name='from' value='{{ $filters['from'] }}' class='mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100'>
            </div>
            <div>
                <label class='text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400'>To</label>
                <input type='date' name='to' value='{{ $filters['to'] }}' class='mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100'>
            </div>
            <div class='flex items-end gap-2'>
                <button type='submit' class='w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700'>Filter</button>
                <a href='{{ route('admin.payrolls.index') }}' class='rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800'>Reset</a>
            </div>
        </div>
    </form>

    <div class='mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900'>
        <table class='min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800'>
            <thead class='bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300'>
                <tr>
                    <th class='px-4 py-3'>Staff</th>
                    <th class='px-4 py-3'>Period</th>
                    <th class='px-4 py-3 text-right'>Amount</th>
                    <th class='px-4 py-3'>Due on</th>
                    <th class='px-4 py-3'>Status</th>
                    <th class='px-4 py-3 text-right'>Actions</th>
                </tr>
            </thead>
            <tbody class='divide-y divide-slate-200 dark:divide-slate-800'>
                @forelse($payrolls as $payroll)
                    <tr class='text-slate-700 dark:text-slate-200'>
                        <td class='px-4 py-3'>
                            <div class='font-semibold text-slate-900 dark:text-slate-100'>{{ optional($payroll->payable)->name ?? 'Unknown' }}</div>
                            <p class='text-xs text-slate-500 dark:text-slate-400'>{{ class_basename($payroll->payable_type) }}</p>
                        </td>
                        <td class='px-4 py-3 text-xs text-slate-500 dark:text-slate-400'>
                            {{ optional($payroll->period_start)->format('M d, Y') ?? '—' }}
                            –
                            {{ optional($payroll->period_end)->format('M d, Y') ?? '—' }}
                        </td>
                        <td class='px-4 py-3 text-right font-semibold text-emerald-600 dark:text-emerald-300'>₹{{ number_format($payroll->amount, 2) }}</td>
                        <td class='px-4 py-3'>{{ optional($payroll->due_on)->format('M d, Y') ?? '—' }}</td>
                        <td class='px-4 py-3'>
                            @php
                                $badgeClasses = [
                                    'paid' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200',
                                    'processing' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200',
                                    'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-200',
                                ][$payroll->status] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700/50 dark:text-slate-300';
                            @endphp
                            <span class='inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasses }}'>{{ ucfirst($payroll->status) }}</span>
                        </td>
                        <td class='px-4 py-3 text-right'>
                            <div class='flex justify-end gap-2'>
                                <a href='{{ route('admin.payrolls.edit', $payroll) }}' class='rounded-lg border border-slate-300 px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800'>Edit</a>
                                <form method='POST' action='{{ route('admin.payrolls.destroy', $payroll) }}' onsubmit="return confirm('Delete this payroll entry?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type='submit' class='rounded-lg border border-rose-300 px-3 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50 dark:border-rose-500/60 dark:text-rose-300 dark:hover:bg-rose-500/10'>Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan='6' class='px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400'>No payroll entries yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class='mt-4'>
        {{ $payrolls->links() }}
    </div>
@endsection
