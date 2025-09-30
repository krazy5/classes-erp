@extends('layouts.admin')

@section('title', 'Edit Payment | ' . config('app.name'))
@section('header', 'Edit Payment Plan')

@section('content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <form method="POST" action="{{ route('admin.payments.update', $payment) }}" class="space-y-6">
                @csrf
                @method('PUT')

                @if($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
                        <p class="font-semibold">Please resolve the validation errors below.</p>
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
                        <select name="student_id" data-behavior="student-search" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" required>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" @selected(old('student_id', $payment->student_id) == $student->id)>{{ $student->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Fee Structure</label>
                        <select name="fee_structure_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">Custom plan</option>
                            @foreach($feeStructures as $structure)
                                <option value="{{ $structure->id }}" @selected(old('fee_structure_id', $payment->fee_structure_id) == $structure->id)>{{ $structure->name }} ({{ number_format($structure->amount, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Notes</label>
                    <textarea name="notes" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" placeholder="Internal remarks or payment expectations...">{{ old('notes', $payment->notes) }}</textarea>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-800 dark:bg-slate-800/40">
                    <div class="grid gap-4 md:grid-cols-4">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total due</p>
                            <p class="mt-1 text-lg font-semibold text-slate-900 dark:text-slate-100">{{ number_format($payment->total_amount, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Collected</p>
                            <p class="mt-1 text-lg font-semibold text-emerald-600 dark:text-emerald-300">{{ number_format($payment->amount_paid, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Outstanding</p>
                            <p class="mt-1 text-lg font-semibold text-rose-600 dark:text-rose-300">{{ number_format($payment->outstanding_amount, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Installments</p>
                            <p class="mt-1 text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $payment->installments()->count() }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Adjust installment amounts, receipts, or status from the payment detail page.</p>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('admin.payments.show', $payment) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Cancel</a>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">Save changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection
