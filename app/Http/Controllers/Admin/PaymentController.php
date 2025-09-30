<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\FeeRecord;
use App\Models\FeeStructure;
use App\Models\Installment;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        $query = FeeRecord::query()
            ->with([
                'student.classGroup',
                'feeStructure',
                'installments' => fn ($relation) => $relation->orderBy('sequence'),
            ])
            ->forTenant($tenantId)
            ->orderByDesc('updated_at');

        $search = trim((string) $request->input('search'));
        $classGroupId = $request->input('class_group_id');
        $status = Str::lower((string) $request->input('status', 'all'));
        $paymentMethod = $request->input('payment_method');
        $dueFrom = $request->input('due_from');
        $dueTo = $request->input('due_to');
        $paidFrom = $request->input('paid_from');
        $paidTo = $request->input('paid_to');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->whereHas('student', function ($studentQuery) use ($search) {
                        $studentQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($classGroupId) {
            $query->whereHas('student', fn ($relation) => $relation->where('class_group_id', $classGroupId));
        }

        if ($paymentMethod) {
            $query->whereHas('installments', fn ($relation) => $relation->where('payment_method', $paymentMethod));
        }

        if ($dueFrom || $dueTo) {
            $from = $dueFrom ? Carbon::parse($dueFrom)->startOfDay() : null;
            $to = $dueTo ? Carbon::parse($dueTo)->endOfDay() : null;

            $query->whereHas('installments', function ($relation) use ($from, $to) {
                if ($from) {
                    $relation->whereDate('due_date', '>=', $from);
                }

                if ($to) {
                    $relation->whereDate('due_date', '<=', $to);
                }
            });
        }

        if ($paidFrom || $paidTo) {
            $from = $paidFrom ? Carbon::parse($paidFrom)->startOfDay() : null;
            $to = $paidTo ? Carbon::parse($paidTo)->endOfDay() : null;

            $query->whereHas('installments', function ($relation) use ($from, $to) {
                $relation->whereNotNull('paid_at');

                if ($from) {
                    $relation->where('paid_at', '>=', $from);
                }

                if ($to) {
                    $relation->where('paid_at', '<=', $to);
                }
            });
        }

        $paymentsCollection = $query->get();

        if ($status && $status !== 'all') {
            $paymentsCollection = $paymentsCollection->filter(fn (FeeRecord $record) => $record->status === $status);
        }

        $paymentsCollection = $paymentsCollection->values();

        $perPage = max(1, (int) $request->input('per_page', 12));
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $slice = $paymentsCollection->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $payments = new LengthAwarePaginator(
            $slice,
            $paymentsCollection->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $summary = [
            'total_due' => round((float) $paymentsCollection->sum(fn (FeeRecord $record) => (float) $record->total_amount), 2),
            'total_collected' => round((float) $paymentsCollection->sum(fn (FeeRecord $record) => $record->amount_paid), 2),
            'total_outstanding' => round((float) $paymentsCollection->sum(fn (FeeRecord $record) => $record->outstanding_amount), 2),
            'records' => $paymentsCollection->count(),
        ];

        $classGroups = ClassGroup::query()
            ->when($tenantId, fn ($builder) => $builder->where('tenant_id', $tenantId))
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('admin.payments.index', [
            'payments' => $payments,
            'summary' => $summary,
            'classGroups' => $classGroups,
            'filters' => [
                'search' => $search,
                'class_group_id' => $classGroupId,
                'status' => $status,
                'payment_method' => $paymentMethod,
                'due_from' => $dueFrom,
                'due_to' => $dueTo,
                'paid_from' => $paidFrom,
                'paid_to' => $paidTo,
                'per_page' => $perPage,
            ],
            'paymentMethods' => $this->paymentMethods(),
        ]);
    }

    public function create(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        return view('admin.payments.create', [
            'students' => Student::query()
                ->when($tenantId, fn ($builder) => $builder->where('tenant_id', $tenantId))
                ->with('classGroup')
                ->orderBy('name')
                ->get(),
            'feeStructures' => FeeStructure::query()
                ->when($tenantId, fn ($builder) => $builder->where('tenant_id', $tenantId))
                ->orderBy('name')
                ->get(),
            'paymentMethods' => $this->paymentMethods(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;

        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'fee_structure_id' => ['nullable', 'exists:fee_structures,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'installments' => ['required', 'array', 'min:1'],
            'installments.*.amount' => ['required', 'numeric', 'min:0'],
            'installments.*.due_date' => ['nullable', 'date'],
            'installments.*.paid_at' => ['nullable', 'date'],
            'installments.*.paid_amount' => ['nullable', 'numeric', 'min:0'],
            'installments.*.payment_method' => ['nullable', 'string', 'max:50'],
            'installments.*.reference' => ['nullable', 'string', 'max:100'],
            'installments.*.receipt_number' => ['nullable', 'string', 'max:100'],
            'installments.*.remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $student = Student::query()
            ->when($tenantId, fn ($builder) => $builder->where('tenant_id', $tenantId))
            ->findOrFail($data['student_id']);

        if (!empty($data['fee_structure_id'])) {
            FeeStructure::query()
                ->when($tenantId, fn ($builder) => $builder->where('tenant_id', $tenantId))
                ->findOrFail($data['fee_structure_id']);
        }

        $installments = collect($data['installments'])
            ->map(fn ($installment) => [
                'amount' => round((float) Arr::get($installment, 'amount', 0), 2),
                'due_date' => Arr::get($installment, 'due_date'),
                'paid_at' => Arr::get($installment, 'paid_at'),
                'paid_amount' => Arr::get($installment, 'paid_amount'),
                'payment_method' => Arr::get($installment, 'payment_method'),
                'reference' => Arr::get($installment, 'reference'),
                'receipt_number' => Arr::get($installment, 'receipt_number'),
                'remarks' => Arr::get($installment, 'remarks'),
            ])
            ->filter(fn ($installment) => $installment['amount'] > 0);

        if ($installments->isEmpty()) {
            return back()->withInput()->withErrors(['installments' => 'Please provide at least one installment with a positive amount.']);
        }

        $totalAmount = round((float) $installments->sum('amount'), 2);

        $feeRecord = FeeRecord::create([
            'tenant_id' => $tenantId,
            'student_id' => $student->id,
            'fee_structure_id' => $data['fee_structure_id'] ?? null,
            'total_amount' => $totalAmount,
            'is_paid' => false,
            'notes' => $data['notes'] ?? null,
        ]);

        $sequence = 1;

        foreach ($installments as $installment) {
            $paidAt = !empty($installment['paid_at']) ? Carbon::parse($installment['paid_at']) : null;

            $feeRecord->installments()->create([
                'sequence' => $sequence++,
                'amount' => $installment['amount'],
                'paid_amount' => $installment['paid_amount'] ?? ($paidAt ? $installment['amount'] : 0),
                'due_date' => $installment['due_date'],
                'paid_at' => $paidAt,
                'payment_method' => $installment['payment_method'] ?? null,
                'reference' => $installment['reference'] ?? null,
                'receipt_number' => $installment['receipt_number'] ?? null,
                'receipt_issued_at' => $paidAt,
                'remarks' => $installment['remarks'] ?? null,
            ]);
        }

        $feeRecord->refreshPaymentStatus();

        return redirect()
            ->route('admin.payments.show', $feeRecord)
            ->with('status', 'Payment plan created successfully.');
    }

    public function show(FeeRecord $payment, Request $request): View
    {
        $payment->loadMissing('student.classGroup', 'feeStructure', 'installments.fines', 'discounts');

        $installments = $payment->installments->sortBy('sequence')->values();

        $metrics = [
            'total' => $payment->total_amount,
            'paid' => $payment->amount_paid,
            'outstanding' => $payment->outstanding_amount,
            'upcoming' => $installments->filter(fn (Installment $installment) => !$installment->is_settled && !$installment->is_overdue)->take(3),
            'overdue_count' => $installments->where('is_overdue', true)->count(),
        ];

        return view('admin.payments.show', [
            'payment' => $payment,
            'installments' => $installments,
            'metrics' => $metrics,
            'paymentMethods' => $this->paymentMethods(),
        ]);
    }

    public function edit(FeeRecord $payment, Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        return view('admin.payments.edit', [
            'payment' => $payment->loadMissing('student', 'feeStructure'),
            'students' => Student::query()
                ->when($tenantId, fn ($builder) => $builder->where('tenant_id', $tenantId))
                ->orderBy('name')
                ->get(),
            'feeStructures' => FeeStructure::query()
                ->when($tenantId, fn ($builder) => $builder->where('tenant_id', $tenantId))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, FeeRecord $payment): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;

        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'fee_structure_id' => ['nullable', 'exists:fee_structures,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        Student::query()
            ->when($tenantId, fn ($builder) => $builder->where('tenant_id', $tenantId))
            ->findOrFail($data['student_id']);

        if (!empty($data['fee_structure_id'])) {
            FeeStructure::query()
                ->when($tenantId, fn ($builder) => $builder->where('tenant_id', $tenantId))
                ->findOrFail($data['fee_structure_id']);
        }

        $payment->update([
            'student_id' => $data['student_id'],
            'fee_structure_id' => $data['fee_structure_id'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $payment->refreshPaymentStatus();

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('status', 'Payment record updated.');
    }

    public function destroy(FeeRecord $payment): RedirectResponse
    {
        $payment->installments()->delete();
        $payment->delete();

        return redirect()
            ->route('admin.payments.index')
            ->with('status', 'Payment record deleted.');
    }

    public function receipt(Request $request, FeeRecord $payment)
    {
        $payment->loadMissing('student.classGroup', 'feeStructure', 'installments');

        $view = view('admin.payments.receipt', [
            'payment' => $payment,
            'installments' => $payment->installments->sortBy('sequence')->values(),
            'generatedAt' => now(),
        ]);

        if ($request->boolean('download')) {
            $studentName = Str::slug(optional($payment->student)->name ?? 'student');
            $filename = "receipt-{$studentName}-{$payment->id}.html";

            return response()->streamDownload(fn () => print($view->render()), $filename, [
                'Content-Type' => 'text/html; charset=UTF-8',
            ]);
        }

        return $view;
    }

    public function installmentReceipt(Request $request, FeeRecord $payment, Installment $installment)
    {
        abort_unless($installment->fee_record_id === $payment->id, 404);

        $payment->loadMissing('student.classGroup');

        $view = view('admin.payments.installment-receipt', [
            'payment' => $payment,
            'installment' => $installment,
            'generatedAt' => now(),
        ]);

        if ($request->boolean('download')) {
            $studentName = Str::slug(optional($payment->student)->name ?? 'student');
            $filename = "receipt-{$studentName}-installment-{$installment->sequence}.html";

            return response()->streamDownload(fn () => print($view->render()), $filename, [
                'Content-Type' => 'text/html; charset=UTF-8',
            ]);
        }

        return $view;
    }

    protected function paymentMethods(): array
    {
        return [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'card' => 'Card',
            'upi' => 'UPI',
            'cheque' => 'Cheque',
            'other' => 'Other',
        ];
    }
}
