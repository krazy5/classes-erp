<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeRecord;
use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentInstallmentController extends Controller
{
    public function store(Request $request, FeeRecord $payment): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'due_date' => ['nullable', 'date'],
            'paid_at' => ['nullable', 'date'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:100'],
            'receipt_number' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:500'],
            'sequence' => ['nullable', 'integer', 'min:1'],
        ]);

        $sequence = $data['sequence'] ?? ($payment->installments()->max('sequence') + 1);

        $paidAt = !empty($data['paid_at']) ? Carbon::parse($data['paid_at']) : null;

        $payment->installments()->create([
            'sequence' => $sequence,
            'amount' => round((float) $data['amount'], 2),
            'paid_amount' => $data['paid_amount'] ?? ($paidAt ? $data['amount'] : 0),
            'due_date' => $data['due_date'] ?? null,
            'paid_at' => $paidAt,
            'payment_method' => $data['payment_method'] ?? null,
            'reference' => $data['reference'] ?? null,
            'receipt_number' => $data['receipt_number'] ?? null,
            'receipt_issued_at' => $paidAt,
            'remarks' => $data['remarks'] ?? null,
        ]);

        $payment->refreshPaymentStatus();

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('status', 'Installment added successfully.');
    }

    public function update(Request $request, FeeRecord $payment, Installment $installment): RedirectResponse
    {
        abort_unless($installment->fee_record_id === $payment->id, 404);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'due_date' => ['nullable', 'date'],
            'paid_at' => ['nullable', 'date'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:100'],
            'receipt_number' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:500'],
            'sequence' => ['nullable', 'integer', 'min:1'],
        ]);

        $paidAt = !empty($data['paid_at']) ? Carbon::parse($data['paid_at']) : null;

        $installment->fill([
            'sequence' => $data['sequence'] ?? $installment->sequence,
            'amount' => round((float) $data['amount'], 2),
            'paid_amount' => $data['paid_amount'] ?? ($paidAt ? $data['amount'] : $installment->paid_amount),
            'due_date' => $data['due_date'] ?? null,
            'paid_at' => $paidAt,
            'payment_method' => $data['payment_method'] ?? null,
            'reference' => $data['reference'] ?? null,
            'receipt_number' => $data['receipt_number'] ?? $installment->receipt_number,
            'receipt_issued_at' => $paidAt ?? $installment->receipt_issued_at,
            'remarks' => $data['remarks'] ?? null,
        ])->save();

        $payment->refreshPaymentStatus();

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('status', 'Installment updated.');
    }

    public function destroy(FeeRecord $payment, Installment $installment): RedirectResponse
    {
        abort_unless($installment->fee_record_id === $payment->id, 404);

        $installment->delete();

        $payment->refreshPaymentStatus();

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('status', 'Installment removed.');
    }

    public function markPaid(Request $request, FeeRecord $payment, Installment $installment): RedirectResponse
    {
        abort_unless($installment->fee_record_id === $payment->id, 404);

        $data = $request->validate([
            'paid_at' => ['required', 'date'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:100'],
            'receipt_number' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $paidAt = Carbon::parse($data['paid_at']);

        $installment->forceFill([
            'paid_at' => $paidAt,
            'paid_amount' => $data['paid_amount'] ?? $installment->amount,
            'payment_method' => $data['payment_method'] ?? $installment->payment_method,
            'reference' => $data['reference'] ?? $installment->reference,
            'receipt_number' => $data['receipt_number'] ?? $installment->receipt_number,
            'receipt_issued_at' => $paidAt,
            'remarks' => $data['remarks'] ?? $installment->remarks,
        ])->save();

        $payment->refreshPaymentStatus();

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('status', 'Installment marked as paid.');
    }
}
