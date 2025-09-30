<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassGroup;
use App\Models\Expense;
use App\Models\Payroll;
use App\Models\Installment;
use App\Models\Student;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('admin.reports.index');
    }

    public function attendance(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $reportType = $request->string('type')->lower()->value() ?? 'class';
        $reportType = in_array($reportType, ['class', 'batch', 'student'], true) ? $reportType : 'class';

        $dateToInput = $request->input('to');
        $dateFromInput = $request->input('from');

        $dateTo = $dateToInput ? Carbon::parse($dateToInput)->endOfDay() : now()->endOfDay();
        $dateFrom = $dateFromInput ? Carbon::parse($dateFromInput)->startOfDay() : (clone $dateTo)->copy()->subDays(29)->startOfDay();

        if ($dateFrom->greaterThan($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
        }

        $classGroupId = $request->input('class_group_id');
        $studentId = $request->input('student_id');

        $classGroups = ClassGroup::when($tenantId, function (Builder $query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->orderBy('name')
            ->pluck('name', 'id');

        $students = Student::when($tenantId, function (Builder $query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($classGroupId, function (Builder $query) use ($classGroupId) {
                $query->where('class_group_id', $classGroupId);
            })
            ->orderBy('name')
            ->pluck('name', 'id');

        $rows = $this->attendanceStats(
            reportType: $reportType,
            tenantId: $tenantId,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            classGroupId: $classGroupId,
            studentId: $studentId
        );

        $chart = [
            'labels' => $rows->pluck('label'),
            'data' => $rows->pluck('rate'),
            'colors' => $this->chartColors($rows->count()),
        ];

        $totalSessions = (int) $rows->sum('total');
        $totalPresent = (int) $rows->sum('present');
        $totalAbsent = (int) $rows->sum('absent');
        $averageRate = $totalSessions > 0 ? round(($totalPresent / $totalSessions) * 100, 1) : 0;

        $summary = [
            'range' => [$dateFrom->toDateString(), $dateTo->toDateString()],
            'segments' => $rows->count(),
            'present' => $totalPresent,
            'absent' => $totalAbsent,
            'average' => $averageRate,
        ];

        return view('admin.reports.attendance', [
            'reportType' => $reportType,
            'dateFrom' => $dateFrom->toDateString(),
            'dateTo' => $dateTo->toDateString(),
            'classGroups' => $classGroups,
            'students' => $students,
            'selectedClassGroup' => $classGroupId,
            'selectedStudent' => $studentId,
            'rows' => $rows,
            'chart' => $chart,
            'summary' => $summary,
        ]);
    }

    public function fees(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $reportType = $request->string('type')->lower()->value() ?? 'class';
        $reportType = in_array($reportType, ['class', 'batch', 'student'], true) ? $reportType : 'class';

        $dateToInput = $request->input('to');
        $dateFromInput = $request->input('from');

        $dateTo = $dateToInput ? Carbon::parse($dateToInput)->endOfDay() : now()->endOfDay();
        $dateFrom = $dateFromInput ? Carbon::parse($dateFromInput)->startOfDay() : (clone $dateTo)->copy()->subDays(29)->startOfDay();

        if ($dateFrom->greaterThan($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
        }

        $classGroupId = $request->input('class_group_id');
        $studentId = $request->input('student_id');

        $classGroups = ClassGroup::when($tenantId, function (Builder $query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->orderBy('name')
            ->pluck('name', 'id');

        $students = Student::when($tenantId, function (Builder $query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($classGroupId, function (Builder $query) use ($classGroupId) {
                $query->where('class_group_id', $classGroupId);
            })
            ->orderBy('name')
            ->pluck('name', 'id');

        $rows = $this->feeStats(
            reportType: $reportType,
            tenantId: $tenantId,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            classGroupId: $classGroupId,
            studentId: $studentId
        );

        $chart = [
            'labels' => $rows->pluck('label'),
            'data' => $rows->pluck('rate'),
            'colors' => $this->chartColors($rows->count()),
        ];

        $totalDue = $rows->sum(fn ($row) => $row['due']);
        $collectedPeriod = $rows->sum(fn ($row) => $row['collected_period']);
        $collectedTotal = $rows->sum(fn ($row) => $row['collected_total']);
        $totalOutstanding = $rows->sum(fn ($row) => $row['outstanding']);
        $averageRate = $totalDue > 0 ? round((($totalDue - $totalOutstanding) / $totalDue) * 100, 1) : 0;

        $summary = [
            'range' => [$dateFrom->toDateString(), $dateTo->toDateString()],
            'segments' => $rows->count(),
            'due' => $totalDue,
            'collected_period' => $collectedPeriod,
            'collected_total' => $collectedTotal,
            'outstanding' => $totalOutstanding,
            'average' => $averageRate,
        ];

        return view('admin.reports.fees', [
            'reportType' => $reportType,
            'dateFrom' => $dateFrom->toDateString(),
            'dateTo' => $dateTo->toDateString(),
            'classGroups' => $classGroups,
            'students' => $students,
            'selectedClassGroup' => $classGroupId,
            'selectedStudent' => $studentId,
            'rows' => $rows,
            'chart' => $chart,
            'summary' => $summary,
        ]);
    }

    public function enquiries(): View
    {
        return view('admin.reports.enquiries');
    }

    protected function attendanceStats(string $reportType, ?int $tenantId, Carbon $dateFrom, Carbon $dateTo, ?int $classGroupId, ?int $studentId): Collection
    {
        $dateStart = $dateFrom->toDateString();
        $dateEnd = $dateTo->toDateString();

        if ($reportType === 'student') {
            $stats = Attendance::query()
                ->selectRaw('students.id as segment_key, students.name as label, class_groups.name as class_name, SUM(CASE WHEN attendances.present = 1 THEN 1 ELSE 0 END) as present_count, COUNT(*) as total_count')
                ->join('students', 'students.id', '=', 'attendances.student_id')
                ->leftJoin('class_groups', 'class_groups.id', '=', 'students.class_group_id')
                ->whereBetween('attendances.date', [$dateStart, $dateEnd])
                ->when($tenantId, fn ($query) => $query->where('attendances.tenant_id', $tenantId))
                ->when($classGroupId, fn ($query) => $query->where('students.class_group_id', $classGroupId))
                ->when($studentId, fn ($query) => $query->where('students.id', $studentId))
                ->groupBy('students.id', 'students.name', 'class_groups.name')
                ->orderBy('students.name')
                ->get();

            return $stats->map(function ($row) {
                $present = (int) $row->present_count;
                $total = (int) $row->total_count;
                $absent = max(0, $total - $present);
                $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0.0;

                return [
                    'label' => $row->label,
                    'class_name' => $row->class_name ?? '—',
                    'present' => $present,
                    'absent' => $absent,
                    'total' => $total,
                    'rate' => $rate,
                ];
            });
        }

        $stats = Attendance::query()
            ->selectRaw('class_groups.id as segment_key, COALESCE(class_groups.name, "Unassigned") as label, SUM(CASE WHEN attendances.present = 1 THEN 1 ELSE 0 END) as present_count, COUNT(*) as total_count')
            ->join('students', 'students.id', '=', 'attendances.student_id')
            ->leftJoin('class_groups', 'class_groups.id', '=', 'students.class_group_id')
            ->whereBetween('attendances.date', [$dateStart, $dateEnd])
            ->when($tenantId, fn ($query) => $query->where('attendances.tenant_id', $tenantId))
            ->when($classGroupId, fn ($query) => $query->where('students.class_group_id', $classGroupId))
            ->groupBy('class_groups.id', 'class_groups.name')
            ->orderByRaw('COALESCE(class_groups.name, "Unassigned") asc')
            ->get();

        $segmentLabel = $reportType === 'batch' ? 'Batch' : 'Class';

        return $stats->map(function ($row) use ($segmentLabel) {
            $present = (int) $row->present_count;
            $total = (int) $row->total_count;
            $absent = max(0, $total - $present);
            $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0.0;

            return [
                'label' => $row->label,
                'segment' => $segmentLabel,
                'class_name' => $row->label,
                'present' => $present,
                'absent' => $absent,
                'total' => $total,
                'rate' => $rate,
            ];
        });
    }

    protected function feeStats(string $reportType, ?int $tenantId, Carbon $dateFrom, Carbon $dateTo, ?int $classGroupId, ?int $studentId): Collection
    {
        $dateStart = $dateFrom->toDateString();
        $dateEnd = $dateTo->toDateString();
        $dateTimeStart = $dateFrom->toDateTimeString();
        $dateTimeEnd = $dateTo->toDateTimeString();

        $rangeFilter = function ($query) use ($dateStart, $dateEnd, $dateTimeStart, $dateTimeEnd) {
            $query->whereBetween('installments.due_date', [$dateStart, $dateEnd])
                ->orWhereBetween('installments.paid_at', [$dateTimeStart, $dateTimeEnd]);
        };

        if ($reportType === 'student') {
            $stats = Installment::query()
                ->selectRaw(
                    "students.id as segment_key,
                    students.name as label,
                    class_groups.name as class_name,
                    SUM(CASE WHEN installments.due_date BETWEEN ? AND ? THEN installments.amount ELSE 0 END) as due_amount,
                    SUM(CASE WHEN installments.paid_at BETWEEN ? AND ? THEN installments.amount ELSE 0 END) as collected_period,
                    SUM(CASE WHEN installments.paid_at IS NOT NULL AND installments.paid_at <= ? THEN installments.amount ELSE 0 END) as collected_to_date",
                    [$dateStart, $dateEnd, $dateTimeStart, $dateTimeEnd, $dateTimeEnd]
                )
                ->join('fee_records', 'fee_records.id', '=', 'installments.fee_record_id')
                ->join('students', 'students.id', '=', 'fee_records.student_id')
                ->leftJoin('class_groups', 'class_groups.id', '=', 'students.class_group_id')
                ->when($tenantId, fn ($query) => $query->where('installments.tenant_id', $tenantId))
                ->when($classGroupId, fn ($query) => $query->where('students.class_group_id', $classGroupId))
                ->when($studentId, fn ($query) => $query->where('students.id', $studentId))
                ->where($rangeFilter)
                ->groupBy('students.id', 'students.name', 'class_groups.name')
                ->orderBy('students.name')
                ->get();

            return $stats->map(function ($row) {
                $due = round((float) $row->due_amount, 2);
                $collectedPeriod = round((float) $row->collected_period, 2);
                $collectedTotalRaw = round((float) $row->collected_to_date, 2);
                $appliedCollected = $due > 0 ? min($collectedTotalRaw, $due) : $collectedTotalRaw;
                $outstanding = round(max(0.0, $due - $appliedCollected), 2);
                $rate = $due > 0 ? round(($appliedCollected / $due) * 100, 1) : 0.0;

                return [
                    'label' => $row->label,
                    'class_name' => $row->class_name ?? '—',
                    'segment' => 'Student',
                    'collected_period' => $collectedPeriod,
                    'collected_total' => $appliedCollected,
                    'outstanding' => $outstanding,
                    'due' => $due,
                    'rate' => $rate,
                ];
            })->filter(function ($row) {
                return ($row['due'] > 0)
                    || ($row['collected_period'] > 0)
                    || ($row['collected_total'] > 0)
                    || ($row['outstanding'] > 0);
            })->values();
        }

        $stats = Installment::query()
            ->selectRaw(
                "class_groups.id as segment_key,
                COALESCE(class_groups.name, 'Unassigned') as label,
                SUM(CASE WHEN installments.due_date BETWEEN ? AND ? THEN installments.amount ELSE 0 END) as due_amount,
                SUM(CASE WHEN installments.paid_at BETWEEN ? AND ? THEN installments.amount ELSE 0 END) as collected_period,
                SUM(CASE WHEN installments.paid_at IS NOT NULL AND installments.paid_at <= ? THEN installments.amount ELSE 0 END) as collected_to_date",
                [$dateStart, $dateEnd, $dateTimeStart, $dateTimeEnd, $dateTimeEnd]
            )
            ->join('fee_records', 'fee_records.id', '=', 'installments.fee_record_id')
            ->join('students', 'students.id', '=', 'fee_records.student_id')
            ->leftJoin('class_groups', 'class_groups.id', '=', 'students.class_group_id')
            ->when($tenantId, fn ($query) => $query->where('installments.tenant_id', $tenantId))
            ->when($classGroupId, fn ($query) => $query->where('students.class_group_id', $classGroupId))
            ->when($studentId, fn ($query) => $query->where('students.id', $studentId))
            ->where($rangeFilter)
            ->groupBy('class_groups.id', 'class_groups.name')
            ->orderByRaw("COALESCE(class_groups.name, 'Unassigned') asc")
            ->get();

        $segmentLabel = $reportType === 'batch' ? 'Batch' : 'Class';

        return $stats->map(function ($row) use ($segmentLabel) {
            $due = round((float) $row->due_amount, 2);
            $collectedPeriod = round((float) $row->collected_period, 2);
            $collectedTotalRaw = round((float) $row->collected_to_date, 2);
            $appliedCollected = $due > 0 ? min($collectedTotalRaw, $due) : $collectedTotalRaw;
            $outstanding = round(max(0.0, $due - $appliedCollected), 2);
            $rate = $due > 0 ? round(($appliedCollected / $due) * 100, 1) : 0.0;

            return [
                'label' => $row->label,
                'segment' => $segmentLabel,
                'class_name' => $row->label,
                'collected_period' => $collectedPeriod,
                'collected_total' => $appliedCollected,
                'outstanding' => $outstanding,
                'due' => $due,
                'rate' => $rate,
            ];
        })->filter(function ($row) {
            return ($row['due'] > 0)
                || ($row['collected_period'] > 0)
                || ($row['collected_total'] > 0)
                || ($row['outstanding'] > 0);
        })->values();
    }

    public function finance(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $dateToInput = $request->input('to');
        $dateFromInput = $request->input('from');

        $dateTo = $dateToInput ? Carbon::parse($dateToInput)->endOfDay() : now()->endOfDay();
        $dateFrom = $dateFromInput ? Carbon::parse($dateFromInput)->startOfDay() : (clone $dateTo)->copy()->subDays(29)->startOfDay();

        if ($dateFrom->greaterThan($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
        }

        $revenueBreakdown = Installment::query()
            ->selectRaw('COALESCE(payment_method, "Unspecified") as label, SUM(paid_amount) as total')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$dateFrom, $dateTo])
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        $revenueTotal = (float) $revenueBreakdown->sum('total');

        $expenseBreakdown = Expense::query()
            ->selectRaw('category, SUM(amount) as total')
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->whereBetween('incurred_on', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        $operationalTotal = (float) $expenseBreakdown->sum('total');

        $payrollBreakdown = Payroll::query()
            ->selectRaw('COALESCE(payable_type, "Other") as label, SUM(amount) as total')
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->where(function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('paid_at', [$dateFrom, $dateTo])
                    ->orWhere(function ($sub) use ($dateFrom, $dateTo) {
                        $sub->whereNull('paid_at')
                            ->whereBetween('due_on', [$dateFrom->toDateString(), $dateTo->toDateString()]);
                    });
            })
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        $payrollTotal = (float) $payrollBreakdown->sum('total');

        $expensesTotal = $operationalTotal + $payrollTotal;
        $netResult = $revenueTotal - $expensesTotal;

        $revenueTimeline = Installment::query()
            ->selectRaw('DATE(paid_at) as day, SUM(paid_amount) as total')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$dateFrom, $dateTo])
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->groupBy('day')
            ->pluck('total', 'day');

        $expenseTimeline = Expense::query()
            ->selectRaw('incurred_on as day, SUM(amount) as total')
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->whereBetween('incurred_on', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->groupBy('day')
            ->pluck('total', 'day');

        $payrollTimeline = Payroll::query()
            ->selectRaw('COALESCE(DATE(paid_at), due_on) as day, SUM(amount) as total')
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->where(function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('paid_at', [$dateFrom, $dateTo])
                    ->orWhere(function ($sub) use ($dateFrom, $dateTo) {
                        $sub->whereNull('paid_at')
                            ->whereBetween('due_on', [$dateFrom->toDateString(), $dateTo->toDateString()]);
                    });
            })
            ->groupBy('day')
            ->pluck('total', 'day');

        $period = CarbonPeriod::create($dateFrom->copy()->startOfDay(), $dateTo->copy()->startOfDay());
        $chart = [
            'labels' => [],
            'revenue' => [],
            'operational' => [],
            'payroll' => [],
            'expenses' => [],
        ];

        foreach ($period as $date) {
            $key = $date->toDateString();
            $chart['labels'][] = $date->format('M d');
            $operational = (float) ($expenseTimeline[$key] ?? 0);
            $payroll = (float) ($payrollTimeline[$key] ?? 0);

            $chart['revenue'][] = (float) ($revenueTimeline[$key] ?? 0);
            $chart['operational'][] = $operational;
            $chart['payroll'][] = $payroll;
            $chart['expenses'][] = $operational + $payroll;
        }

        $summary = [
            'range' => [$dateFrom->toDateString(), $dateTo->toDateString()],
            'revenue' => round($revenueTotal, 2),
            'operational' => round($operationalTotal, 2),
            'payroll' => round($payrollTotal, 2),
            'expenses' => round($expensesTotal, 2),
            'net' => round($netResult, 2),
            'status' => $netResult >= 0 ? 'profit' : 'loss',
        ];

        return view('admin.reports.finance', [
            'dateFrom' => $dateFrom->toDateString(),
            'dateTo' => $dateTo->toDateString(),
            'revenueBreakdown' => $revenueBreakdown,
            'expenseBreakdown' => $expenseBreakdown,
            'payrollBreakdown' => $payrollBreakdown,
            'chart' => $chart,
            'summary' => $summary,
        ]);
    }
    protected function chartColors(int $count): array
    {
        $palette = [
            '#4f46e5', '#06b6d4', '#10b981', '#f59e0b', '#fb7185', '#8b5cf6', '#f97316', '#14b8a6', '#6366f1', '#ef4444',
        ];

        if ($count <= 0) {
            return $palette;
        }

        $colors = [];
        for ($i = 0; $i < $count; $i++) {
            $colors[] = $palette[$i % count($palette)];
        }

        return $colors;
    }
}








