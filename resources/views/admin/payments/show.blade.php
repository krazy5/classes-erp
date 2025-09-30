@extends('layouts.admin')

@section('title', 'Payment Details | ' . config('app.name'))
@section('header', 'Payment Plan Overview')

@section('content')
    <div class="space-y-8" x-data="{ addOpen: false }">
        @if(session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ $payment->student->name ?? 'Student record missing' }}</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Class: {{ $payment->student->classGroup->name ?? 'N/A' }} | Plan #{{ str_pad($payment->id, 4, '0', STR_PAD_LEFT) }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.payments.edit', $payment) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Edit Plan</a>
                    <a href="{{ route('admin.payments.receipt', $payment) }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 px-4 py-2 text-sm font-semibold text-emerald-600 transition hover:bg-emerald-50 dark:border-emerald-500/40 dark:text-emerald-200 dark:hover:bg-emerald-500/10">View Receipt</a>
                    <a href="{{ route('admin.payments.receipt', [$payment, 'download' => true]) }}" class="inline-flex items-center gap-2 rounded-lg border border-indigo-300 px-4 py-2 text-sm font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:border-indigo-500/40 dark:text-indigo-200 dark:hover:bg-indigo-500/10">Download</a>
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Print</button>
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/60">
                    <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total due</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format($metrics['total'], 2) }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Net after adjustments: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ number_format($payment->net_amount, 2) }}</span></p>
                </div>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
                    <p class="text-xs uppercase tracking-wide">Collected</p>
                    <p class="mt-2 text-2xl font-semibold">{{ number_format($metrics['paid'], 2) }}</p>
                </div>
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
                    <p class="text-xs uppercase tracking-wide">Outstanding</p>
                    <p class="mt-2 text-2xl font-semibold">{{ number_format($metrics['outstanding'], 2) }}</p>
                    <p class="mt-1 text-xs text-rose-600/80 dark:text-rose-200/80">Net outstanding: <span class="font-semibold">{{ number_format($payment->net_outstanding, 2) }}</span></p>
                </div>
                <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 text-indigo-700 dark:border-indigo-500/40 dark:bg-indigo-500/10 dark:text-indigo-200">
                    <p class="text-xs uppercase tracking-wide">Status</p>
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
                    <span class="mt-2 inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasses }}">{{ ucfirst($status) }}</span>
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
                    <p class="text-xs uppercase tracking-wide">Discounts applied</p>
                    <p class="mt-2 text-xl font-semibold">{{ number_format($payment->discount_total, 2) }}</p>
                    <p class="text-xs text-emerald-600/80 dark:text-emerald-200/80">Scholarships and concessions granted to this plan.</p>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-700 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-200">
                    <p class="text-xs uppercase tracking-wide">Fines assessed</p>
                    <p class="mt-2 text-xl font-semibold">{{ number_format($payment->fine_total, 2) }}</p>
                    <p class="text-xs text-amber-600/80 dark:text-amber-200/80">Outstanding fines: {{ number_format($payment->installments->sum(fn ($inst) => $inst->fine_outstanding), 2) }}</p>
                </div>
            </div>

            <dl class="mt-6 grid gap-4 md:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Fee structure</dt>
                    <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $payment->feeStructure->name ?? 'Custom plan' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Created</dt>
                    <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $payment->created_at?->format('d M Y H:i') ?? '—' }}</dd>
                </div>
                <div class="md:col-span-2">
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Notes</dt>
                    <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $payment->notes ?: '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Installments</h2>
                <button type="button" @click="addOpen = !addOpen" class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 px-3 py-2 text-sm font-semibold text-emerald-600 transition hover:bg-emerald-50 dark:border-emerald-500/40 dark:text-emerald-200 dark:hover:bg-emerald-500/10">
                    @svg('heroicon-o-plus', 'h-4 w-4')
                    Add installment
                </button>
            </div>

            <div x-show="addOpen" x-transition class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40">
                <form method="POST" action="{{ route('admin.payments.installments.store', $payment) }}" class="grid gap-3 md:grid-cols-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Amount<span class="text-rose-500">*</span></label>
                        <input type="number" name="amount" step="0.01" min="0.01" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Due date</label>
                        <input type="date" name="due_date" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Sequence</label>
                        <input type="number" name="sequence" min="1" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" placeholder="Auto">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Payment method</label>
                        <select name="payment_method" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">Select</option>
                            @foreach($paymentMethods as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Reference</label>
                        <input type="text" name="reference" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div class="md:col-span-3 flex justify-end gap-2">
                        <button type="button" @click="addOpen = false" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Cancel</button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">Add installment</button>
                    </div>
                </form>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Amount</th>
                            <th class="px-4 py-3">Due date</th>
                            <th class="px-4 py-3">Paid amount</th>
                            <th class="px-4 py-3">Paid at</th>
                            <th class="px-4 py-3">Method</th>
                            <th class="px-4 py-3">Reference</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($installments as $installment)
                            <tr class="align-top text-slate-700 dark:text-slate-200" x-data="{ editing: false, marking: false }">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $installment->sequence }}</td>
                                <td class="px-4 py-3">{{ number_format($installment->amount, 2) }}</td>
                                <td class="px-4 py-3">{{ $installment->due_date?->format('d M Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-emerald-600 dark:text-emerald-300">{{ number_format($installment->paid_amount, 2) }}</td>
                                <td class="px-4 py-3">{{ $installment->paid_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $installment->payment_method ? ucfirst(str_replace('_', ' ', $installment->payment_method)) : '—' }}</td>
                                <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">{{ $installment->reference ?: '—' }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $instStatus = $installment->status;
                                        $instBadge = [
                                            'settled' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200',
                                            'partial' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200',
                                            'overdue' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-200',
                                            'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-200',
                                        ][$instStatus] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700/50 dark:text-slate-300';
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $instBadge }}">{{ ucfirst($instStatus) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" @click="editing = !editing">Edit</button>
                                        @if(!$installment->is_settled)
                                            <button type="button" class="inline-flex items-center rounded-lg border border-emerald-300 px-3 py-1 text-xs font-semibold text-emerald-600 transition hover:bg-emerald-50 dark:border-emerald-500/40 dark:text-emerald-200 dark:hover:bg-emerald-500/10" @click="marking = !marking">Mark paid</button>
                                        @endif
                                        @if($installment->is_settled)
                                            <a href="{{ route('admin.payments.installments.receipt', [$payment, $installment]) }}" target="_blank" class="inline-flex items-center rounded-lg border border-indigo-300 px-3 py-1 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:border-indigo-500/40 dark:text-indigo-200 dark:hover:bg-indigo-500/10">Receipt</a>
                                        @endif
                                        <form method="POST" action="{{ route('admin.payments.installments.destroy', [$payment, $installment]) }}" onsubmit="return confirm('Delete this installment?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center rounded-lg border border-rose-300 px-3 py-1 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/40 dark:text-rose-200 dark:hover:bg-rose-500/10">Delete</button>
                                        </form>
                                    </div>

                                    <div x-show="editing" x-transition class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-left dark:border-slate-700 dark:bg-slate-800/40">
                                        <form method="POST" action="{{ route('admin.payments.installments.update', [$payment, $installment]) }}" class="grid gap-3 md:grid-cols-2">
                                            @csrf
                                            @method('PUT')
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Sequence</label>
                                                <input type="number" name="sequence" value="{{ $installment->sequence }}" min="1" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Amount</label>
                                                <input type="number" name="amount" value="{{ $installment->amount }}" step="0.01" min="0.01" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Due date</label>
                                                <input type="date" name="due_date" value="{{ optional($installment->due_date)->toDateString() }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Paid at</label>
                                                <input type="date" name="paid_at" value="{{ optional($installment->paid_at)->toDateString() }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Paid amount</label>
                                                <input type="number" name="paid_amount" value="{{ $installment->paid_amount }}" step="0.01" min="0" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Method</label>
                                                <select name="payment_method" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                                                    <option value="">Select</option>
                                                    @foreach($paymentMethods as $value => $label)
                                                        <option value="{{ $value }}" @selected($installment->payment_method === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Reference</label>
                                                <input type="text" name="reference" value="{{ $installment->reference }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Receipt #</label>
                                                <input type="text" name="receipt_number" value="{{ $installment->receipt_number }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" placeholder="Auto-generated if blank">
                                            </div>
                                            <div class="md:col-span-2">
                                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Remarks</label>
                                                <textarea name="remarks" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">{{ $installment->remarks }}</textarea>
                                            </div>
                                            <div class="md:col-span-2 flex justify-end gap-2">
                                                <button type="button" @click="editing = false" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Cancel</button>
                                                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700">Save changes</button>
                                            </div>
                                        </form>
                                    </div>

                                    <div x-show="marking" x-transition class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-left dark:border-emerald-500/40 dark:bg-emerald-500/10">
                                        <form method="POST" action="{{ route('admin.payments.installments.mark-paid', [$payment, $installment]) }}" class="grid gap-3 md:grid-cols-2">
                                            @csrf
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wide">Paid at<span class="text-rose-500">*</span></label>
                                                <input type="date" name="paid_at" value="{{ now()->toDateString() }}" required class="mt-1 w-full rounded-lg border border-emerald-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-emerald-500/60 dark:bg-emerald-500/10 dark:text-emerald-100">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wide">Amount received</label>
                                                <input type="number" name="paid_amount" step="0.01" min="0" value="{{ $installment->amount }}" class="mt-1 w-full rounded-lg border border-emerald-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-emerald-500/60 dark:bg-emerald-500/10 dark:text-emerald-100">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wide">Method</label>
                                                <select name="payment_method" class="mt-1 w-full rounded-lg border border-emerald-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-emerald-500/60 dark:bg-emerald-500/10 dark:text-emerald-100">
                                                    <option value="">Select</option>
                                                    @foreach($paymentMethods as $value => $label)
                                                        <option value="{{ $value }}" @selected($installment->payment_method === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wide">Reference</label>
                                                <input type="text" name="reference" value="{{ $installment->reference }}" class="mt-1 w-full rounded-lg border border-emerald-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-emerald-500/60 dark:bg-emerald-500/10 dark:text-emerald-100">
                                            </div>
                                            <div class="md:col-span-2">
                                                <label class="block text-xs font-semibold uppercase tracking-wide">Remarks</label>
                                                <textarea name="remarks" rows="2" class="mt-1 w-full rounded-lg border border-emerald-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-emerald-500/60 dark:bg-emerald-500/10 dark:text-emerald-100">{{ $installment->remarks }}</textarea>
                                            </div>
                                            <div class="md:col-span-2 flex justify-end gap-2">
                                                <button type="button" @click="marking = false" class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-500/40 dark:text-emerald-200 dark:hover:bg-emerald-500/20">Cancel</button>
                                                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">Confirm payment</button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">No installments added yet. Use the button above to get started.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8 space-y-6">
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-500/40 dark:bg-amber-500/10">
                    <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-200">Late payment fines</h3>
                    <p class="text-xs text-amber-700/70 dark:text-amber-200/80">Track penalties applied to each installment and settle them as needed.</p>

                    <div class="mt-4 space-y-4">
                        @forelse($payment->installments as $installment)
                            <div class="rounded-xl border border-amber-200/60 bg-white p-4 shadow-sm dark:border-amber-500/30 dark:bg-slate-900/60">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Installment #{{ $installment->sequence }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Due {{ optional($installment->due_date)->format('d M Y') ?? '—' }} • Amount ₹{{ number_format($installment->amount, 2) }}</p>
                                    </div>
                                    <p class="text-xs font-medium text-amber-600 dark:text-amber-300">Outstanding fines: {{ number_format($installment->fine_outstanding, 2) }}</p>
                                </div>

                                <div class="mt-3 space-y-3">
                                    @forelse($installment->fines as $fine)
                                        <div class="rounded-lg border border-amber-200/70 bg-amber-50/50 p-3 dark:border-amber-500/30 dark:bg-amber-500/10">
                                            <div class="flex flex-wrap items-center justify-between gap-3">
                                                <div>
                                                    <p class="text-sm font-semibold text-amber-700 dark:text-amber-200">₹{{ number_format($fine->amount, 2) }}</p>
                                                    <p class="text-xs text-amber-600/80 dark:text-amber-200/70">
                                                        Assessed {{ optional($fine->assessed_at)->format('d M Y') ?? '—' }}
                                                        @if($fine->reason)
                                                            • {{ $fine->reason }}
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    @if(!$fine->is_paid && !$fine->is_waived)
                                                        <form method="POST" action="{{ route('admin.payments.installments.fines.mark-paid', [$payment, $installment, $fine]) }}">
                                                            @csrf
                                                            <input type="hidden" name="paid_amount" value="{{ $fine->outstanding_amount }}">
                                                            <button type="submit" class="rounded-lg border border-emerald-300 px-3 py-1 text-xs font-semibold text-emerald-600 transition hover:bg-emerald-50 dark:border-emerald-500/40 dark:text-emerald-200 dark:hover:bg-emerald-500/10">Mark paid</button>
                                                        </form>
                                                    @endif
                                                    <form method="POST" action="{{ route('admin.payments.installments.fines.destroy', [$payment, $installment, $fine]) }}" onsubmit="return confirm('Remove this fine?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="rounded-lg border border-rose-300 px-3 py-1 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/40 dark:text-rose-200 dark:hover:bg-rose-500/10">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                            <p class="mt-1 text-xs text-amber-600/80 dark:text-amber-200/70">
                                                Status: {{ $fine->is_paid ? 'Paid' : ($fine->is_waived ? 'Waived' : 'Pending') }} • Outstanding ₹{{ number_format($fine->outstanding_amount, 2) }}
                                            </p>
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-500 dark:text-slate-400">No fines recorded for this installment.</p>
                                    @endforelse

                                    <form method="POST" action="{{ route('admin.payments.installments.fines.store', [$payment, $installment]) }}" class="grid gap-2 rounded-lg border border-amber-200/70 bg-white p-3 text-xs dark:border-amber-500/30 dark:bg-slate-900/60 md:grid-cols-4">
                                        @csrf
                                        <div>
                                            <label class="block font-semibold text-amber-700 dark:text-amber-200">Amount</label>
                                            <input type="number" step="0.01" min="0" name="amount" required class="mt-1 w-full rounded border border-amber-300 px-2 py-1 text-xs focus:border-amber-500 focus:ring-amber-500 dark:border-amber-500/50 dark:bg-amber-500/10 dark:text-amber-100">
                                        </div>
                                        <div>
                                            <label class="block font-semibold text-amber-700 dark:text-amber-200">Assessed on</label>
                                            <input type="date" name="assessed_at" class="mt-1 w-full rounded border border-amber-300 px-2 py-1 text-xs focus:border-amber-500 focus:ring-amber-500 dark:border-amber-500/50 dark:bg-amber-500/10 dark:text-amber-100">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block font-semibold text-amber-700 dark:text-amber-200">Reason</label>
                                            <input type="text" name="reason" class="mt-1 w-full rounded border border-amber-300 px-2 py-1 text-xs focus:border-amber-500 focus:ring-amber-500 dark:border-amber-500/50 dark:bg-amber-500/10 dark:text-amber-100" placeholder="e.g. Late payment">
                                        </div>
                                        <div class="md:col-span-4">
                                            <label class="block font-semibold text-amber-700 dark:text-amber-200">Notes</label>
                                            <textarea name="notes" rows="2" class="mt-1 w-full rounded border border-amber-300 px-2 py-1 text-xs focus:border-amber-500 focus:ring-amber-500 dark:border-amber-500/50 dark:bg-amber-500/10 dark:text-amber-100" placeholder="Additional context"></textarea>
                                        </div>
                                        <div class="md:col-span-4 flex justify-end">
                                            <button type="submit" class="rounded-lg bg-amber-600 px-3 py-1 text-xs font-semibold text-white transition hover:bg-amber-700">Add fine</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-amber-700 dark:text-amber-200">Add installments to start tracking fines.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-500/40 dark:bg-emerald-500/10">
                    <h3 class="text-sm font-semibold text-emerald-700 dark:text-emerald-200">Discounts</h3>
                    <p class="text-xs text-emerald-600/80 dark:text-emerald-200/80">Scholarships and concessions applied to this payment plan.</p>

                    <div class="mt-4 space-y-3">
                        @forelse($payment->discounts as $discount)
                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-emerald-300/70 bg-white p-3 shadow-sm dark:border-emerald-500/40 dark:bg-slate-900/60">
                                <div>
                                    <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-200">&#8377;{{ number_format($discount->amount, 2) }} {{ $discount->type ? '(' . $discount->type . ')' : '' }}</p>
                                    <p class="text-xs text-emerald-600/80 dark:text-emerald-200/70">Granted {{ optional($discount->granted_at)->format('d M Y') ?? 'N/A' }} @if($discount->reason) | {{ $discount->reason }} @endif</p>
                                </div>
                                <form method="POST" action="{{ route('admin.payments.discounts.destroy', [$payment, $discount]) }}" onsubmit="return confirm('Remove this discount?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-rose-300 px-3 py-1 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/40 dark:text-rose-200 dark:hover:bg-rose-500/10">Delete</button>
                                </form>
                            </div>
                        @empty
                            <p class="text-xs text-emerald-600/80 dark:text-emerald-200/70">No discounts recorded yet.</p>
                        @endforelse

                        <form method="POST" action="{{ route('admin.payments.discounts.store', $payment) }}" class="grid gap-2 rounded-lg border border-emerald-200/70 bg-white p-3 text-xs dark:border-emerald-500/30 dark:bg-slate-900/60 md:grid-cols-4">
                            @csrf
                            <div>
                                <label class="block font-semibold text-emerald-700 dark:text-emerald-200">Amount</label>
                                <input type="number" step="0.01" min="0" name="amount" required class="mt-1 w-full rounded border border-emerald-300 px-2 py-1 text-xs focus:border-emerald-500 focus:ring-emerald-500 dark:border-emerald-500/50 dark:bg-emerald-500/10 dark:text-emerald-100">
                            </div>
                            <div>
                                <label class="block font-semibold text-emerald-700 dark:text-emerald-200">Type</label>
                                <input type="text" name="type" class="mt-1 w-full rounded border border-emerald-300 px-2 py-1 text-xs focus:border-emerald-500 focus:ring-emerald-500 dark:border-emerald-500/50 dark:bg-emerald-500/10 dark:text-emerald-100" placeholder="Scholarship">
                            </div>
                            <div>
                                <label class="block font-semibold text-emerald-700 dark:text-emerald-200">Granted on</label>
                                <input type="date" name="granted_at" class="mt-1 w-full rounded border border-emerald-300 px-2 py-1 text-xs focus:border-emerald-500 focus:ring-emerald-500 dark:border-emerald-500/50 dark:bg-emerald-500/10 dark:text-emerald-100">
                            </div>
                            <div>
                                <label class="block font-semibold text-emerald-700 dark:text-emerald-200">Reason</label>
                                <input type="text" name="reason" class="mt-1 w-full rounded border border-emerald-300 px-2 py-1 text-xs focus:border-emerald-500 focus:ring-emerald-500 dark:border-emerald-500/50 dark:bg-emerald-500/10 dark:text-emerald-100" placeholder="Merit-based">
                            </div>
                            <div class="md:col-span-4">
                                <label class="block font-semibold text-emerald-700 dark:text-emerald-200">Notes</label>
                                <textarea name="notes" rows="2" class="mt-1 w-full rounded border border-emerald-300 px-2 py-1 text-xs focus:border-emerald-500 focus:ring-emerald-500 dark:border-emerald-500/50 dark:bg-emerald-500/10 dark:text-emerald-100" placeholder="Additional context"></textarea>
                            </div>
                            <div class="md:col-span-4 flex justify-end">
                                <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1 text-xs font-semibold text-white transition hover:bg-emerald-700">Add discount</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
