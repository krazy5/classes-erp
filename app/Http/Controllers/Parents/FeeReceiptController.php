<?php

namespace App\Http\Controllers\Parents;

use App\Http\Controllers\Controller;
use App\Models\Installment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FeeReceiptController extends Controller
{
    public function show(Request $request, Installment $installment)
    {
        $user = $request->user();

        abort_unless($user->can('fees.view.student'), 403);

        $installment->loadMissing('feeRecord.student.user', 'feeRecord.student.classGroup', 'feeRecord.feeStructure');

        $payment = $installment->feeRecord;
        $student = optional($payment)->student;
        $studentUser = optional($student)->user;

        if (!$payment || !$student || !$studentUser) {
            abort(404);
        }

        $tenantId = $user->tenant_id;

        if ($tenantId && $payment->tenant_id && $payment->tenant_id !== $tenantId) {
            abort(403);
        }

        $isGuardian = $user->students()
            ->where('users.id', $studentUser->id)
            ->exists();

        if ($studentUser->id !== $user->id && !$isGuardian) {
            abort(403);
        }

        $view = view('admin.payments.installment-receipt', [
            'payment' => $payment,
            'installment' => $installment,
            'generatedAt' => now(),
        ]);

        if ($request->boolean('download')) {
            $studentName = Str::slug($student->name ?? 'student');
            $filename = "receipt-{$studentName}-installment-{$installment->sequence}.html";

            return response()->streamDownload(fn () => print($view->render()), $filename, [
                'Content-Type' => 'text/html; charset=UTF-8',
            ]);
        }

        return $view;
    }
}
