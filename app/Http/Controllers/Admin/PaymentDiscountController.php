<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\FeeRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentDiscountController extends Controller
{
    public function store(Request $request, FeeRecord $payment): RedirectResponse
    {
        $this->authorizeTenant($request, $payment);

        $data = $this->validated($request);

        Discount::create(array_merge($data, [
            'tenant_id' => $request->user()->tenant_id,
            'fee_record_id' => $payment->id,
            'granted_by' => $request->user()->id,
        ]));

        return redirect()->route('admin.payments.show', $payment)
            ->with('status', 'Discount recorded successfully.');
    }

    public function destroy(Request $request, FeeRecord $payment, Discount $discount): RedirectResponse
    {
        $this->authorizeTenant($request, $payment);

        abort_unless($discount->fee_record_id === $payment->id, 404);

        $discount->delete();

        return redirect()->route('admin.payments.show', $payment)
            ->with('status', 'Discount entry removed.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['nullable', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
            'granted_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    protected function authorizeTenant(Request $request, FeeRecord $payment): void
    {
        $tenantId = $request->user()->tenant_id;

        if ($tenantId && $payment->tenant_id !== $tenantId) {
            abort(403);
        }
    }
}
