<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $status = $request->string('status')->trim();
        $from = $request->date('from');
        $to = $request->date('to');

        $query = Payroll::query()
            ->with('payable')
            ->when($tenantId, fn ($builder) => $builder->where('tenant_id', $tenantId))
            ->when($status->isNotEmpty(), fn ($builder) => $builder->where('status', $status))
            ->when($from, fn ($builder) => $builder->whereDate('period_start', '>=', $from))
            ->when($to, fn ($builder) => $builder->whereDate('period_end', '<=', $to))
            ->orderByDesc('due_on')
            ->orderByDesc('created_at');

        $total = (clone $query)->sum('amount');

        $payrolls = $query->paginate(20)->withQueryString();

        return view('admin.finance.payrolls.index', [
            'payrolls' => $payrolls,
            'summary' => [
                'total' => round((float) $total, 2),
                'count' => $payrolls->total(),
            ],
            'filters' => [
                'status' => $status->value(),
                'from' => $from?->format('Y-m-d'),
                'to' => $to?->format('Y-m-d'),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.finance.payrolls.create', $this->formData($request));
    }

    public function store(Request $request): RedirectResponse
    {
        [$payableType, $payableId] = $this->parsePayable($request);

        $data = $this->validated($request);

        Payroll::create(array_merge($data, [
            'tenant_id' => $request->user()->tenant_id,
            'payable_type' => $payableType,
            'payable_id' => $payableId,
        ]));

        return redirect()->route('admin.payrolls.index')
            ->with('status', 'Payroll entry recorded.');
    }

    public function edit(Request $request, Payroll $payroll): View
    {
        $this->authorizeTenant($request, $payroll->tenant_id);

        return view('admin.finance.payrolls.edit', array_merge(
            ['payroll' => $payroll],
            $this->formData($request)
        ));
    }

    public function update(Request $request, Payroll $payroll): RedirectResponse
    {
        $this->authorizeTenant($request, $payroll->tenant_id);
        [$payableType, $payableId] = $this->parsePayable($request);
        $data = $this->validated($request);

        $payroll->update(array_merge($data, [
            'payable_type' => $payableType,
            'payable_id' => $payableId,
        ]));

        return redirect()->route('admin.payrolls.index')
            ->with('status', 'Payroll entry updated.');
    }

    public function destroy(Request $request, Payroll $payroll): RedirectResponse
    {
        $this->authorizeTenant($request, $payroll->tenant_id);

        $payroll->delete();

        return redirect()->route('admin.payrolls.index')
            ->with('status', 'Payroll entry removed.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'payable' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date'],
            'due_on' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:pending,processing,paid'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    protected function parsePayable(Request $request): array
    {
        $value = $request->string('payable')->toString();

        if (!str_contains($value, ':')) {
            abort(422, 'Invalid payable selection.');
        }

        [$type, $id] = explode(':', $value, 2);

        return match ($type) {
            'teacher' => [Teacher::class, (int) $id],
            default => [User::class, (int) $id],
        };
    }

    protected function formData(Request $request): array
    {
        $tenantId = $request->user()->tenant_id;

        $teachers = Teacher::with('user')
            ->when($tenantId, fn ($builder) => $builder->where('tenant_id', $tenantId))
            ->orderBy('name')
            ->get();

        $staff = User::query()
            ->when($tenantId, fn ($builder) => $builder->where('tenant_id', $tenantId))
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'teacher'))
            ->orderBy('name')
            ->get();

        return compact('teachers', 'staff');
    }

    protected function authorizeTenant(Request $request, ?int $tenantId): void
    {
        $current = $request->user()->tenant_id;

        if ($current && $tenantId !== $current) {
            abort(403);
        }
    }
}
