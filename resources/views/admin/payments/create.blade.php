@extends('layouts.admin')

@section('title', 'Create Payment | ' . config('app.name'))
@section('header', 'New Payment Plan')

@section('content')
    @php
        $initialInstallments = collect(old('installments', []))->map(function ($item) {
            return [
                'amount' => $item['amount'] ?? '',
                'due_date' => $item['due_date'] ?? '',
                'paid_at' => $item['paid_at'] ?? '',
                'paid_amount' => $item['paid_amount'] ?? '',
                'payment_method' => $item['payment_method'] ?? '',
                'reference' => $item['reference'] ?? '',
                'receipt_number' => $item['receipt_number'] ?? '',
                'remarks' => $item['remarks'] ?? '',
            ];
        });

        if ($initialInstallments->isEmpty()) {
            $initialInstallments = collect(range(1, 3))->map(fn () => [
                'amount' => '',
                'due_date' => '',
                'paid_at' => '',
                'paid_amount' => '',
                'payment_method' => '',
                'reference' => '',
                'receipt_number' => '',
                'remarks' => '',
            ]);
        }
    @endphp

    <div class="space-y-8" x-data="paymentPlanForm({ installments: {{ $initialInstallments->toJson() }} })">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <form method="POST" action="{{ route('admin.payments.store') }}" class="space-y-6">
                @csrf

                @if($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
                        <p class="font-semibold">Please correct the highlighted fields.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Student<span class="text-rose-500">*</span></label>
                        <select name="student_id" data-behavior="student-search" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                            <option value="" disabled @selected(!old('student_id'))>Select a student</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>
                                    {{ $student->name }} @if($student->classGroup) — {{ $student->classGroup->name }} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Fee Structure</label>
                        <select name="fee_structure_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">Custom plan</option>
                            @foreach($feeStructures as $structure)
                                <option value="{{ $structure->id }}" @selected(old('fee_structure_id') == $structure->id)>{{ $structure->name }} ({{ number_format($structure->amount, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Notes</label>
                    <textarea name="notes" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" placeholder="Internal remarks or payment expectations...">{{ old('notes') }}</textarea>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Installment schedule</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Adjust amounts and dates as needed. Totals update automatically.</p>
                    </div>
                    <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Total due: <span x-text="formattedTotal()"></span></div>
                </div>

                <div class="space-y-4">
                    <template x-for="(installment, index) in installments" :key="index">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Installment <span x-text="index + 1"></span></h3>
                                <button type="button" class="text-xs font-semibold text-rose-600 hover:text-rose-700 disabled:text-slate-400" @click="remove(index)" x-bind:disabled="installments.length === 1">Remove</button>
                            </div>
                            <div class="mt-4 grid gap-3 md:grid-cols-3">
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Amount<span class="text-rose-500">*</span></label>
                                    <input type="number" step="0.01" min="0.01" :name="`installments[${index}][amount]`" x-model="installment.amount" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Due date</label>
                                    <input type="date" :name="`installments[${index}][due_date]`" x-model="installment.due_date" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Paid date</label>
                                    <input type="date" :name="`installments[${index}][paid_at]`" x-model="installment.paid_at" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Paid amount</label>
                                    <input type="number" step="0.01" min="0" :name="`installments[${index}][paid_amount]`" x-model="installment.paid_amount" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Payment method</label>
                                    <select :name="`installments[${index}][payment_method]`" x-model="installment.payment_method" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                                        <option value="">Select</option>
                                        @foreach($paymentMethods as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Reference</label>
                                    <input type="text" :name="`installments[${index}][reference]`" x-model="installment.reference" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Receipt #</label>
                                    <input type="text" :name="`installments[${index}][receipt_number]`" x-model="installment.receipt_number" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" placeholder="Auto-generated if blank">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Remarks</label>
                                    <textarea :name="`installments[${index}][remarks]`" x-model="installment.remarks" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" placeholder="Optional notes for this installment"></textarea>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-between">
                    <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 px-3 py-2 text-sm font-semibold text-emerald-600 transition hover:bg-emerald-50 dark:border-emerald-500/40 dark:text-emerald-200 dark:hover:bg-emerald-500/10" @click="add()">
                        @svg('heroicon-o-plus', 'h-4 w-4')
                        Add installment
                    </button>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Save Payment Plan
                    </button>
                </div>
            </form>
        </section>
    </div>

    @push('scripts')
        <script>
            function paymentPlanForm({ installments }) {
                return {
                    installments,
                    add() {
                        this.installments.push({
                            amount: '',
                            due_date: '',
                            paid_at: '',
                            paid_amount: '',
                            payment_method: '',
                            reference: '',
                            receipt_number: '',
                            remarks: '',
                        });
                    },
                    remove(index) {
                        if (this.installments.length === 1) {
                            return;
                        }
                        this.installments.splice(index, 1);
                    },
                    formattedTotal() {
                        const total = this.installments.reduce((carry, installment) => {
                            const amount = parseFloat(installment.amount);
                            return carry + (isNaN(amount) ? 0 : amount);
                        }, 0);

                        return new Intl.NumberFormat(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(total);
                    }
                };
            }
        </script>
    @endpush
@endsection
