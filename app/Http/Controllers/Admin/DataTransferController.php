<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DatasetExport;
use App\Http\Controllers\Controller;
use App\Imports\DatasetArrayImport;
use App\Models\ClassGroup;
use App\Models\Expense;
use App\Models\FeeRecord;
use App\Models\FeeStructure;
use App\Models\Installment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DataTransferController extends Controller
{
    private array $datasets = [
        'students' => [
            'label' => 'Students',
            'description' => 'Student directory with class assignments.',
            'headings' => [
                'id' => 'ID',
                'name' => 'Name',
                'email' => 'Email',
                'phone' => 'Phone',
                'dob' => 'Date of Birth',
                'gender' => 'Gender',
                'address' => 'Address',
                'class_group' => 'Class Group',
                'created_at' => 'Created At',
                'updated_at' => 'Updated At',
                'deleted_at' => 'Deleted At',
            ],
        ],
        'fee_records' => [
            'label' => 'Fee Records',
            'description' => 'Fee plans with collection status.',
            'headings' => [
                'id' => 'ID',
                'student_email' => 'Student Email',
                'student_name' => 'Student Name',
                'fee_structure' => 'Fee Structure',
                'total_amount' => 'Total Amount',
                'is_paid' => 'Is Paid',
                'amount_paid' => 'Amount Paid',
                'outstanding_amount' => 'Outstanding Amount',
                'status' => 'Status',
                'notes' => 'Notes',
                'created_at' => 'Created At',
                'updated_at' => 'Updated At',
                'deleted_at' => 'Deleted At',
            ],
        ],
        'installments' => [
            'label' => 'Installments',
            'description' => 'Fee installment schedule and payment state.',
            'headings' => [
                'id' => 'ID',
                'fee_record_id' => 'Fee Record ID',
                'student_email' => 'Student Email',
                'sequence' => 'Sequence',
                'amount' => 'Amount',
                'paid_amount' => 'Paid Amount',
                'due_date' => 'Due Date',
                'paid_at' => 'Paid At',
                'payment_method' => 'Payment Method',
                'reference' => 'Reference',
                'receipt_number' => 'Receipt Number',
                'receipt_issued_at' => 'Receipt Issued At',
                'remarks' => 'Remarks',
                'status' => 'Status',
                'created_at' => 'Created At',
                'updated_at' => 'Updated At',
                'deleted_at' => 'Deleted At',
            ],
        ],
        'expenses' => [
            'label' => 'Expenses',
            'description' => 'Operational expense ledger.',
            'headings' => [
                'id' => 'ID',
                'category' => 'Category',
                'title' => 'Title',
                'amount' => 'Amount',
                'incurred_on' => 'Incurred On',
                'payment_method' => 'Payment Method',
                'reference' => 'Reference',
                'notes' => 'Notes',
                'recorded_by_email' => 'Recorded By (Email)',
                'created_at' => 'Created At',
                'updated_at' => 'Updated At',
                'deleted_at' => 'Deleted At',
            ],
        ],
    ];

    public function index(Request $request)
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        return view('admin.data-transfer.index', [
            'datasets' => $this->datasets,
        ]);
    }

    public function export(Request $request): BinaryFileResponse|JsonResponse
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        $validated = $request->validate([
            'dataset' => ['required', Rule::in(array_keys($this->datasets))],
            'format' => ['required', Rule::in(['json', 'xlsx'])],
        ]);

        $tenantId = (int) $request->user()->tenant_id;
        $datasetKey = $validated['dataset'];
        $format = $validated['format'];

        $records = $this->collectDataset($datasetKey, $tenantId);
        $columns = array_keys($this->datasets[$datasetKey]['headings']);

        if ($format === 'json') {
            return response()->json([
                'dataset' => $datasetKey,
                'generated_at' => now()->toIso8601String(),
                'rows' => $records->map(fn (array $row) => Arr::only($row, $columns))->values(),
            ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        $rows = $records->map(function (array $row) use ($columns) {
            return array_map(fn ($column) => $row[$column] ?? null, $columns);
        })->all();

        $headings = array_values($this->datasets[$datasetKey]['headings']);
        $title = $this->datasets[$datasetKey]['label'];
        $filename = sprintf('%s-%s.xlsx', $datasetKey, now()->format('Ymd-His'));

        return Excel::download(new DatasetExport($rows, $headings, $title), $filename);
    }

    public function import(Request $request): \Illuminate\Http\RedirectResponse
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        $validated = $request->validate([
            'dataset' => ['required', Rule::in(array_keys($this->datasets))],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $datasetKey = $validated['dataset'];
        $file = $validated['file'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, ['json', 'xlsx', 'xls'], true)) {
            return back()->withErrors(['file' => 'Unsupported file format. Upload a JSON or Excel file.']);
        }

        if ($extension === 'json') {
            $decoded = json_decode($file->get(), true);
            if (!is_array($decoded)) {
                return back()->withErrors(['file' => 'Invalid JSON payload.']);
            }

            $rows = collect($decoded['rows'] ?? $decoded)
                ->filter(fn ($row) => is_array($row))
                ->map(fn ($row) => array_change_key_case($row, CASE_LOWER))
                ->values()
                ->all();
        } else {
            $import = new DatasetArrayImport();
            Excel::import($import, $file);
            $rows = ($import->rows ?? collect())
                ->map(fn ($row) => array_change_key_case($row, CASE_LOWER))
                ->all();
        }

        if (empty($rows)) {
            return back()->withErrors(['file' => 'No records detected in the uploaded file.']);
        }

        $tenantId = (int) $request->user()->tenant_id;

        $result = match ($datasetKey) {
            'students' => $this->importStudents($rows, $tenantId),
            'fee_records' => $this->importFeeRecords($rows, $tenantId),
            'installments' => $this->importInstallments($rows, $tenantId),
            'expenses' => $this->importExpenses($rows, $tenantId),
            default => ['imported' => 0, 'updated' => 0, 'skipped' => count($rows)],
        };

        return back()->with('dataTransferImport', [
            'dataset' => $datasetKey,
            'summary' => $result,
        ]);
    }

    protected function collectDataset(string $dataset, int $tenantId): Collection
    {
        return match ($dataset) {
            'students' => Student::withTrashed()
                ->with('classGroup')
                ->where('tenant_id', $tenantId)
                ->orderBy('id')
                ->get()
                ->map(function (Student $student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->name,
                        'email' => $student->email,
                        'phone' => $student->phone,
                        'dob' => optional($student->dob)->format('Y-m-d'),
                        'gender' => $student->gender,
                        'address' => $student->address,
                        'class_group' => $student->classGroup?->name,
                        'created_at' => optional($student->created_at)->toDateTimeString(),
                        'updated_at' => optional($student->updated_at)->toDateTimeString(),
                        'deleted_at' => optional($student->deleted_at)->toDateTimeString(),
                    ];
                }),
            'fee_records' => FeeRecord::withTrashed()
                ->with(['student', 'feeStructure'])
                ->where('tenant_id', $tenantId)
                ->orderBy('id')
                ->get()
                ->map(function (FeeRecord $record) {
                    return [
                        'id' => $record->id,
                        'student_email' => $record->student?->email,
                        'student_name' => $record->student?->name,
                        'fee_structure' => $record->feeStructure?->name,
                        'total_amount' => (float) ($record->total_amount ?? 0),
                        'is_paid' => $record->is_paid ? 1 : 0,
                        'amount_paid' => (float) $record->amount_paid,
                        'outstanding_amount' => (float) $record->outstanding_amount,
                        'status' => $record->status,
                        'notes' => $record->notes,
                        'created_at' => optional($record->created_at)->toDateTimeString(),
                        'updated_at' => optional($record->updated_at)->toDateTimeString(),
                        'deleted_at' => optional($record->deleted_at)->toDateTimeString(),
                    ];
                }),
            'installments' => Installment::withTrashed()
                ->with(['feeRecord.student'])
                ->whereHas('feeRecord', fn ($query) => $query->where('tenant_id', $tenantId))
                ->orderBy('fee_record_id')
                ->orderBy('sequence')
                ->get()
                ->map(function (Installment $installment) {
                    return [
                        'id' => $installment->id,
                        'fee_record_id' => $installment->fee_record_id,
                        'student_email' => $installment->feeRecord?->student?->email,
                        'sequence' => $installment->sequence,
                        'amount' => (float) ($installment->amount ?? 0),
                        'paid_amount' => (float) ($installment->paid_amount ?? 0),
                        'due_date' => optional($installment->due_date)->format('Y-m-d'),
                        'paid_at' => optional($installment->paid_at)->toDateTimeString(),
                        'payment_method' => $installment->payment_method,
                        'reference' => $installment->reference,
                        'receipt_number' => $installment->receipt_number,
                        'receipt_issued_at' => optional($installment->receipt_issued_at)->toDateTimeString(),
                        'remarks' => $installment->remarks,
                        'status' => $installment->status,
                        'created_at' => optional($installment->created_at)->toDateTimeString(),
                        'updated_at' => optional($installment->updated_at)->toDateTimeString(),
                        'deleted_at' => optional($installment->deleted_at)->toDateTimeString(),
                    ];
                }),
            'expenses' => Expense::withTrashed()
                ->with('recordedBy')
                ->where('tenant_id', $tenantId)
                ->orderByDesc('incurred_on')
                ->orderByDesc('id')
                ->get()
                ->map(function (Expense $expense) {
                    return [
                        'id' => $expense->id,
                        'category' => $expense->category,
                        'title' => $expense->title,
                        'amount' => (float) ($expense->amount ?? 0),
                        'incurred_on' => optional($expense->incurred_on)->format('Y-m-d'),
                        'payment_method' => $expense->payment_method,
                        'reference' => $expense->reference,
                        'notes' => $expense->notes,
                        'recorded_by_email' => $expense->recordedBy?->email,
                        'created_at' => optional($expense->created_at)->toDateTimeString(),
                        'updated_at' => optional($expense->updated_at)->toDateTimeString(),
                        'deleted_at' => optional($expense->deleted_at)->toDateTimeString(),
                    ];
                }),
            default => collect(),
        };
    }

    protected function importStudents(array $rows, int $tenantId): array
    {
        $imported = $updated = $skipped = 0;

        DB::transaction(function () use ($rows, $tenantId, &$imported, &$updated, &$skipped) {
            foreach ($rows as $row) {
                $email = strtolower(trim($this->extractValue($row, ['email']) ?? ''));
                $name = trim($this->extractValue($row, ['name']) ?? '');

                if ($email === '' || $name === '') {
                    $skipped++;
                    continue;
                }

                $classGroupName = trim($this->extractValue($row, ['class_group']) ?? '');
                $classGroupId = null;
                if ($classGroupName !== '') {
                    $classGroup = ClassGroup::withTrashed()->firstOrCreate(
                        ['tenant_id' => $tenantId, 'name' => $classGroupName],
                        ['tenant_id' => $tenantId, 'name' => $classGroupName]
                    );
                    $classGroupId = $classGroup->id;
                }

                $data = [
                    'tenant_id' => $tenantId,
                    'name' => $name,
                    'email' => $email,
                    'phone' => $this->extractValue($row, ['phone']),
                    'dob' => $this->parseDate($this->extractValue($row, ['dob', 'date_of_birth'])),
                    'gender' => $this->extractValue($row, ['gender']),
                    'address' => $this->extractValue($row, ['address']),
                    'class_group_id' => $classGroupId,
                ];

                $timestamps = [
                    'created_at' => $this->parseDateTime($this->extractValue($row, ['created_at'])),
                    'updated_at' => $this->parseDateTime($this->extractValue($row, ['updated_at'])) ?? now(),
                    'deleted_at' => $this->parseDateTime($this->extractValue($row, ['deleted_at'])),
                ];

                $student = Student::withTrashed()
                    ->where('tenant_id', $tenantId)
                    ->where('email', $email)
                    ->first();

                $isNew = false;
                if (!$student) {
                    $student = new Student();
                    $student->tenant_id = $tenantId;
                    $student->email = $email;
                    if (!empty($row['id']) && !$student->newQuery()->whereKey($row['id'])->exists()) {
                        $student->setAttribute('id', (int) $row['id']);
                    }
                    $isNew = true;
                }

                $this->saveModel($student, $data, $timestamps);

                $isNew ? $imported++ : $updated++;
            }
        });

        return compact('imported', 'updated', 'skipped');
    }

    protected function importFeeRecords(array $rows, int $tenantId): array
    {
        $imported = $updated = $skipped = 0;

        DB::transaction(function () use ($rows, $tenantId, &$imported, &$updated, &$skipped) {
            $students = Student::withTrashed()
                ->where('tenant_id', $tenantId)
                ->get()
                ->keyBy(fn (Student $student) => strtolower($student->email));

            $structures = FeeStructure::withTrashed()
                ->where('tenant_id', $tenantId)
                ->get()
                ->keyBy(fn (FeeStructure $structure) => strtolower($structure->name));

            foreach ($rows as $row) {
                $studentEmail = strtolower(trim($row['student_email'] ?? ''));
                $student = $students[$studentEmail] ?? null;

                if (!$student) {
                    $skipped++;
                    continue;
                }

                $structureName = trim($row['fee_structure'] ?? '');
                $feeStructureId = null;
                if ($structureName !== '') {
                    $key = strtolower($structureName);
                    $structure = $structures[$key] ?? null;
                    if (!$structure) {
                        $structure = FeeStructure::create([
                            'tenant_id' => $tenantId,
                            'name' => $structureName,
                            'amount' => $row['total_amount'] ?? 0,
                            'is_active' => true,
                        ]);
                        $structures[$key] = $structure;
                    }
                    $feeStructureId = $structure->id;
                }

                $data = [
                    'tenant_id' => $tenantId,
                    'student_id' => $student->id,
                    'fee_structure_id' => $feeStructureId,
                    'total_amount' => $this->parseFloat($row['total_amount'] ?? null),
                    'is_paid' => $this->parseBoolean($row['is_paid'] ?? null) ?? false,
                    'notes' => $row['notes'] ?? null,
                ];

                $timestamps = [
                    'created_at' => $this->parseDateTime($row['created_at'] ?? null),
                    'updated_at' => $this->parseDateTime($row['updated_at'] ?? null) ?? now(),
                    'deleted_at' => $this->parseDateTime($row['deleted_at'] ?? null),
                ];

                $record = FeeRecord::withTrashed()
                    ->where('tenant_id', $tenantId)
                    ->where('id', $row['id'] ?? 0)
                    ->first();

                $isNew = false;
                if (!$record) {
                    $record = new FeeRecord();
                    $record->tenant_id = $tenantId;
                    if (!empty($row['id'])) {
                        $record->setAttribute('id', (int) $row['id']);
                    }
                    $isNew = true;
                }

                $this->saveModel($record, $data, $timestamps);

                $isNew ? $imported++ : $updated++;
            }
        });

        return compact('imported', 'updated', 'skipped');
    }

    protected function importInstallments(array $rows, int $tenantId): array
    {
        $imported = $updated = $skipped = 0;

        DB::transaction(function () use ($rows, $tenantId, &$imported, &$updated, &$skipped) {
            $feeRecords = FeeRecord::withTrashed()
                ->where('tenant_id', $tenantId)
                ->get()
                ->keyBy('id');

            foreach ($rows as $row) {
                $feeRecordId = (int) ($row['fee_record_id'] ?? 0);
                $feeRecord = $feeRecords[$feeRecordId] ?? null;

                if (!$feeRecord) {
                    $skipped++;
                    continue;
                }

                $data = [
                    'tenant_id' => $tenantId,
                    'fee_record_id' => $feeRecordId,
                    'sequence' => (int) ($row['sequence'] ?? 0),
                    'amount' => $this->parseFloat($row['amount'] ?? null),
                    'paid_amount' => $this->parseFloat($row['paid_amount'] ?? null),
                    'due_date' => $this->parseDate($row['due_date'] ?? null),
                    'paid_at' => $this->parseDateTime($row['paid_at'] ?? null),
                    'payment_method' => $row['payment_method'] ?? null,
                    'reference' => $row['reference'] ?? null,
                    'receipt_number' => $row['receipt_number'] ?? null,
                    'receipt_issued_at' => $this->parseDateTime($row['receipt_issued_at'] ?? null),
                    'remarks' => $row['remarks'] ?? null,
                ];

                $timestamps = [
                    'created_at' => $this->parseDateTime($row['created_at'] ?? null),
                    'updated_at' => $this->parseDateTime($row['updated_at'] ?? null) ?? now(),
                    'deleted_at' => $this->parseDateTime($row['deleted_at'] ?? null),
                ];

                $installment = Installment::withTrashed()
                    ->where('tenant_id', $tenantId)
                    ->where('id', $row['id'] ?? 0)
                    ->first();

                $isNew = false;
                if (!$installment) {
                    $installment = new Installment();
                    $installment->tenant_id = $tenantId;
                    if (!empty($row['id'])) {
                        $installment->setAttribute('id', (int) $row['id']);
                    }
                    $isNew = true;
                }

                $this->saveModel($installment, $data, $timestamps, true);

                $isNew ? $imported++ : $updated++;
            }
        });

        return compact('imported', 'updated', 'skipped');
    }

    protected function importExpenses(array $rows, int $tenantId): array
    {
        $imported = $updated = $skipped = 0;

        DB::transaction(function () use ($rows, $tenantId, &$imported, &$updated, &$skipped) {
            $users = User::query()
                ->get()
                ->keyBy(fn (User $user) => strtolower($user->email));

            foreach ($rows as $row) {
                $data = [
                    'tenant_id' => $tenantId,
                    'category' => $row['category'] ?? null,
                    'title' => $row['title'] ?? null,
                    'amount' => $this->parseFloat($row['amount'] ?? null),
                    'incurred_on' => $this->parseDate($row['incurred_on'] ?? null),
                    'payment_method' => $row['payment_method'] ?? null,
                    'reference' => $row['reference'] ?? null,
                    'notes' => $row['notes'] ?? null,
                    'recorded_by' => null,
                ];

                $email = strtolower(trim($row['recorded_by_email'] ?? ''));
                if ($email !== '' && isset($users[$email])) {
                    $data['recorded_by'] = $users[$email]->id;
                }

                $timestamps = [
                    'created_at' => $this->parseDateTime($row['created_at'] ?? null),
                    'updated_at' => $this->parseDateTime($row['updated_at'] ?? null) ?? now(),
                    'deleted_at' => $this->parseDateTime($row['deleted_at'] ?? null),
                ];

                $expense = Expense::withTrashed()
                    ->where('tenant_id', $tenantId)
                    ->where('id', $row['id'] ?? 0)
                    ->first();

                $isNew = false;
                if (!$expense) {
                    $expense = new Expense();
                    $expense->tenant_id = $tenantId;
                    if (!empty($row['id'])) {
                        $expense->setAttribute('id', (int) $row['id']);
                    }
                    $isNew = true;
                }

                $this->saveModel($expense, $data, $timestamps);

                $isNew ? $imported++ : $updated++;
            }
        });

        return compact('imported', 'updated', 'skipped');
    }

    protected function saveModel(Model $model, array $attributes, array $timestamps, bool $withEvents = false): void
    {
        $model->forceFill($attributes);

        foreach (['created_at', 'updated_at', 'deleted_at'] as $key) {
            if (!empty($timestamps[$key])) {
                $model->setAttribute($key, $timestamps[$key]);
            }
        }

        if (empty($timestamps['created_at']) && !$model->getAttribute('created_at')) {
            $model->setAttribute('created_at', now());
        }

        if (empty($timestamps['updated_at'])) {
            $model->setAttribute('updated_at', now());
        }

        if (!array_key_exists('deleted_at', $timestamps)) {
            $model->setAttribute('deleted_at', null);
        }

        $original = $model->timestamps;
        $model->timestamps = false;

        $withEvents ? $model->save() : $model->saveQuietly();

        $model->timestamps = $original;
    }

    protected function parseDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    protected function parseDateTime(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value));
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function parseBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower((string) $value);

        return match ($normalized) {
            '1', 'true', 'yes', 'y' => true,
            '0', 'false', 'no', 'n' => false,
            default => null,
        };
    }

    protected function parseFloat(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) $value;
    }

    protected function extractValue(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) {
                $value = $row[$key];

                return $value === '' ? null : $value;
            }
        }

        return null;
    }
}
