<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeRecord;
use App\Models\Fine;
use App\Models\Installment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InstallmentFineController extends Controller
{
    public function store(Request $request, FeeRecord $payment, Installment $installment): RedirectResponse
    {
        $this->assertAssociations($request, $payment, $installment);

        $data = $this->validated($request);

        Fine::create(array_merge($data, [
            'tenant_id' => $request->user()->tenant_id,
            'installment_id' => $installment->id,
        ]));

        return redirect()->route('admin.payments.show', $payment)
            ->with('status', 'Fine recorded successfully.');
    }

    public function update(Request $request, FeeRecord $payment, Installment $installment, Fine $fine): RedirectResponse
    {
        $this->assertAssociations($request, $payment, $installment, $fine);

        $data = $this->validated($request);

        $fine->update($data);

        return redirect()->route('admin.payments.show', $payment)
            ->with('status', 'Fine updated successfully.');
    }

    public function destroy(Request $request, FeeRecord $payment, Installment $installment, Fine $fine): RedirectResponse
    {
        $this->assertAssociations($request, $payment, $installment, $fine);

        $fine->delete();

        return redirect()->route('admin.payments.show', $payment)
            ->with('status', 'Fine entry removed.');
    }

    public function markPaid(Request $request, FeeRecord $payment, Installment $installment, Fine $fine): RedirectResponse
    {
        $this->assertAssociations($request, $payment, $installment, $fine);

        $amount = (float) $request->input('paid_amount', $fine->amount);
        $fine->markPaid($amount);

        return redirect()->route('admin.payments.show', $payment)
            ->with('status', 'Fine marked as paid.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'assessed_at' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    protected function assertAssociations(Request $request, FeeRecord $payment, Installment $installment, ?Fine $fine = null): void
    {
        $tenantId = $request->user()->tenant_id;

        if ($tenantId && $payment->tenant_id !== $tenantId) {
            abort(403);
        }

        abort_unless($installment->fee_record_id === $payment->id, 404);

        if ($fine) {
            abort_unless($fine->installment_id === $installment->id, 404);
        }
    }
}
