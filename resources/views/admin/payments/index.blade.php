@extends('layouts.admin')

@section('title', 'Payments | ' . config('app.name'))
@section('header', 'Payments Management')

@section('content')
    <div class="space-y-8">
        @if(session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm text-slate-600 dark:text-slate-400">Monitor fee schedules, outstanding balances, and receipts.</p>
                <p class="text-xs text-slate-500 dark:text-slate-500">Totals reflect the current filter selection.</p>
            </div>
            <a href="{{ route('admin.payments.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                @svg('heroicon-o-plus', 'h-4 w-4')
                New Payment Plan
            </a>
        </div>

        <section class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/60">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Records</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format($summary['records']) }}</p>
            </div>
            <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 text-indigo-700 dark:border-indigo-500/40 dark:bg-indigo-500/10 dark:text-indigo-200">
                <p class="text-xs uppercase tracking-wide">Total Due</p>
                <p class="mt-2 text-2xl font-semibold">{{ number_format($summary['total_due'], 2) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
                <p class="text-xs uppercase tracking-wide">Collected</p>
                <p class="mt-2 text-2xl font-semibold">{{ number_format($summary['total_collected'], 2) }}</p>
            </div>
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
                <p class="text-xs uppercase tracking-wide">Outstanding</p>
                <p class="mt-2 text-2xl font-semibold">{{ number_format($summary['total_outstanding'], 2) }}</p>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <form method="GET" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Search</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Student, email, phone or notes" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</label>
                    <select name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                        @php
                            $statuses = [
                                'all' => 'All',
                                'pending' => 'Pending',
                                'partial' => 'Partial',
                                'overdue' => 'Overdue',
                                'paid' => 'Paid',
                                'draft' => 'Draft',
                            ];
                        @endphp
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? 'all') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Class Group</label>
                    <select name="class_group_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">All</option>
                        @foreach($classGroups as $id => $name)
                            <option value="{{ $id }}" @selected((string) ($filters['class_group_id'] ?? '') === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Method</label>
                    <select name="payment_method" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">All</option>
                        @foreach($paymentMethods as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['payment_method'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Due from</label>
                    <input type="date" name="due_from" value="{{ $filters['due_from'] ?? '' }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Due to</label>
                    <input type="date" name="due_to" value="{{ $filters['due_to'] ?? '' }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Paid from</label>
                    <input type="date" name="paid_from" value="{{ $filters['paid_from'] ?? '' }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Paid to</label>
                    <input type="date" name="paid_to" value="{{ $filters['paid_to'] ?? '' }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Per page</label>
                    <input type="number" name="per_page" value="{{ $filters['per_page'] ?? 12 }}" min="5" max="50" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                </div>
                <div class="lg:col-span-2 flex items-end gap-2">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-200">Apply Filters</button>
                    <a href="{{ route('admin.payments.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Reset</a>
                </div>
            </form>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Payment Records</h2>
                <span class="text-xs text-slate-500 dark:text-slate-400">Sorted by recent activity</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">Student</th>
                            <th class="px-4 py-3">Class</th>
                            <th class="px-4 py-3">Fee Structure</th>
                            <th class="px-4 py-3 text-right">Due</th>
                            <th class="px-4 py-3 text-right">Collected</th>
                            <th class="px-4 py-3 text-right">Outstanding</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Updated</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($payments as $payment)
                            <tr class="text-slate-700 dark:text-slate-200">
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $payment->student->name ?? '—' }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Plan #{{ str_pad($payment->id, 4, '0', STR_PAD_LEFT) }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $payment->student->classGroup->name ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $payment->feeStructure->name ?? 'Custom Plan' }}</td>
                                <td class="px-4 py-3 text-right font-medium">{{ number_format($payment->total_amount, 2) }}</td>
                                <td class="px-4 py-3 text-right text-emerald-600 dark:text-emerald-300">{{ number_format($payment->amount_paid, 2) }}</td>
                                <td class="px-4 py-3 text-right text-rose-600 dark:text-rose-300">{{ number_format($payment->outstanding_amount, 2) }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $status = $payment->status;
                                        $badgeClasses = [
                                            'paid' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200',
                                            'partial' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200',
                                            'overdue' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-200',
                                            'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-200',
                                            'draft' => 'bg-slate-100 text-slate-600 dark:bg-slate-700/50 dark:text-slate-300',
                                        ][$status] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700/50 dark:text-slate-300';
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasses }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">{{ $payment->updated_at?->diffForHumans() ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.payments.show', $payment) }}" class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">View</a>
                                        <a href="{{ route('admin.payments.receipt', $payment) }}" target="_blank" class="inline-flex items-center rounded-lg border border-emerald-300 px-3 py-1 text-xs font-semibold text-emerald-600 transition hover:bg-emerald-50 dark:border-emerald-500/40 dark:text-emerald-200 dark:hover:bg-emerald-500/10">Receipt</a>
                                        <form method="POST" action="{{ route('admin.payments.destroy', $payment) }}" onsubmit="return confirm('Delete this payment plan? This will remove all installments.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center rounded-lg border border-rose-300 px-3 py-1 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/40 dark:text-rose-200 dark:hover:bg-rose-500/10">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">No payment records match the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                {{ $payments->links() }}
            </div>
        </section>
    </div>
@endsection
