@extends('layouts.admin')

@section('title', $student->name . ' payments | ' . config('app.name'))
@section('header', 'Student Payments')

@section('content')
    <div class='flex flex-col gap-4 md:flex-row md:items-center md:justify-between'>
        <div>
            <h2 class='text-xl font-semibold text-gray-900 dark:text-gray-100'>{{ $student->name }}</h2>
            <p class='text-sm text-gray-500 dark:text-gray-400'>
                {{ $student->classGroup->name ?? 'No class assigned' }}
            </p>
        </div>
        <div class='flex flex-wrap items-center gap-2'>
            <a href='{{ route('admin.students.show', $student) }}'
               class='rounded border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800'>
                Back to profile
            </a>
            <a href='{{ route('admin.payments.create', ['student_id' => $student->id]) }}'
               class='rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700'>
                Create payment plan
            </a>
        </div>
    </div>

    @if(session('status'))
        <div class='mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-600 dark:bg-green-900/40 dark:text-green-300'>
            {{ session('status') }}
        </div>
    @endif

    <div class='mt-6 space-y-6'>
        @forelse($payments as $payment)
            <div class='rounded-lg bg-white p-6 shadow dark:bg-gray-900'>
                <div class='flex flex-col gap-4 md:flex-row md:items-start md:justify-between'>
                    <div>
                        <div class='flex items-center gap-2 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400'>
                            <span>Status:</span>
                            <span class='rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200'>
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                        <h3 class='mt-2 text-lg font-semibold text-gray-900 dark:text-gray-100'>
                            {{ $payment->feeStructure->name ?? 'Custom plan' }}
                        </h3>
                        <p class='text-sm text-gray-500 dark:text-gray-400'>Created {{ $payment->created_at->format('M d, Y') }}</p>
                    </div>
                    <div class='flex flex-col gap-1 text-sm text-gray-700 dark:text-gray-200'>
                        <span>Total: <strong class='text-gray-900 dark:text-gray-100'>{{ number_format($payment->total_amount, 2) }}</strong></span>
                        <span>Collected: <strong class='text-emerald-600 dark:text-emerald-400'>{{ number_format($payment->amount_paid, 2) }}</strong></span>
                        <span>Due: <strong class='text-amber-600 dark:text-amber-400'>{{ number_format($payment->outstanding_amount, 2) }}</strong></span>
                    </div>
                </div>

                <div class='mt-4 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700'>
                    <table class='min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700'>
                        <thead class='bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-300'>
                            <tr>
                                <th class='px-4 py-2'>Installment</th>
                                <th class='px-4 py-2'>Due date</th>
                                <th class='px-4 py-2'>Amount</th>
                                <th class='px-4 py-2'>Paid</th>
                                <th class='px-4 py-2'>Status</th>
                                <th class='px-4 py-2 text-right'>Actions</th>
                            </tr>
                        </thead>
                        <tbody class='divide-y divide-gray-200 dark:divide-gray-700'>
                            @forelse($payment->installments as $installment)
                                <tr class='text-gray-700 dark:text-gray-200'>
                                    <td class='px-4 py-2'>#{{ $installment->sequence }}</td>
                                    <td class='px-4 py-2'>{{ optional($installment->due_date)->format('M d, Y') ?? 'N/A' }}</td>
                                    <td class='px-4 py-2'>{{ number_format($installment->amount, 2) }}</td>
                                    <td class='px-4 py-2'>{{ number_format($installment->paid_amount, 2) }}</td>
                                    <td class='px-4 py-2'>
                                        @if($installment->is_settled)
                                            <span class='rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300'>Paid</span>
                                        @elseif($installment->is_overdue)
                                            <span class='rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700 dark:bg-red-900/60 dark:text-red-300'>Overdue</span>
                                        @else
                                            <span class='rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-900/50 dark:text-amber-300'>Pending</span>
                                        @endif
                                    </td>
                                    <td class='px-4 py-2 text-right'>
                                        <a href='{{ route('admin.payments.show', $payment) }}'
                                           class='text-xs font-medium text-indigo-600 hover:text-indigo-500'>
                                            Manage
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan='6' class='px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400'>No installments recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class='mt-4 flex flex-wrap items-center gap-3 text-sm'>
                    <a href='{{ route('admin.payments.show', $payment) }}'
                       class='rounded border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800'>
                        Open payment plan
                    </a>
                    <a href='{{ route('admin.payments.edit', $payment) }}'
                       class='rounded border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800'>
                        Edit payment
                    </a>
                </div>
            </div>
        @empty
            <div class='rounded-lg border border-dashed border-gray-300 bg-white p-10 text-center text-sm text-gray-600 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300'>
                No payment records yet. Use the "Create payment plan" button to get started.
            </div>
        @endforelse
    </div>
@endsection
