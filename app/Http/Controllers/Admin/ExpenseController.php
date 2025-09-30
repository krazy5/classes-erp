<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $category = $request->string('category')->trim();
        $from = $request->date('from');
        $to = $request->date('to');

        $query = Expense::query()
            ->when($tenantId, fn ($builder) => $builder->where('tenant_id', $tenantId))
            ->when($category->isNotEmpty(), fn ($builder) => $builder->where('category', 'like', '%'.$category.'%'))
            ->when($from, fn ($builder) => $builder->whereDate('incurred_on', '>=', $from))
            ->when($to, fn ($builder) => $builder->whereDate('incurred_on', '<=', $to))
            ->orderByDesc('incurred_on')
            ->orderByDesc('created_at');

        $total = (clone $query)->sum('amount');

        $expenses = $query->with('media')->paginate(20)->withQueryString();

        return view('admin.finance.expenses.index', [
            'expenses' => $expenses,
            'summary' => [
                'total' => round((float) $total, 2),
                'count' => $expenses->total(),
            ],
            'filters' => [
                'category' => $category->value(),
                'from' => $from?->format('Y-m-d'),
                'to' => $to?->format('Y-m-d'),
            ],
            'categorySuggestions' => $this->categoryOptions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.finance.expenses.create', [
            'expense' => new Expense(),
            'categorySuggestions' => $this->categoryOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $expense = Expense::create(array_merge($data, [
            'tenant_id' => $request->user()->tenant_id,
            'recorded_by' => $request->user()->id,
        ]));

        $this->syncAttachments($expense, $request->file('attachments', []));

        return redirect()->route('admin.expenses.index')
            ->with('status', 'Expense recorded successfully.');
    }

    public function edit(Request $request, Expense $expense): View
    {
        $this->authorizeTenant($request, $expense->tenant_id);

        return view('admin.finance.expenses.edit', [
            'expense' => $expense->load('media'),
            'categorySuggestions' => $this->categoryOptions(),
        ]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorizeTenant($request, $expense->tenant_id);

        $data = $this->validated($request);

        $expense->update($data);

        $this->syncAttachments(
            $expense,
            $request->file('attachments', []),
            $request->input('remove_attachments', [])
        );

        return redirect()->route('admin.expenses.index')
            ->with('status', 'Expense updated successfully.');
    }

    public function destroy(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorizeTenant($request, $expense->tenant_id);

        $expense->delete();

        return redirect()->route('admin.expenses.index')
            ->with('status', 'Expense removed.');
    }

    public function download(Request $request, Expense $expense, Media $media)
    {
        $this->authorizeTenant($request, $expense->tenant_id);

        abort_unless($media->model_id === $expense->id && $media->model_type === Expense::class, 404);

        return $media->toInlineResponse($request);
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'numeric', 'min:0'],
            'incurred_on' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'attachments.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'remove_attachments' => ['array'],
            'remove_attachments.*' => ['integer'],
        ]);
    }

    protected function authorizeTenant(Request $request, ?int $tenantId): void
    {
        $currentTenant = $request->user()->tenant_id;

        if ($currentTenant && $tenantId !== $currentTenant) {
            abort(403);
        }
    }

    protected function syncAttachments(Expense $expense, array $newFiles = [], array $removals = []): void
    {
        if (!empty($removals)) {
            $expense->media()
                ->whereIn('id', $removals)
                ->get()
                ->each->delete();
        }

        collect($newFiles)
            ->filter(fn ($file) => $file instanceof UploadedFile)
            ->each(fn (UploadedFile $file) => $expense->addMedia($file)->toMediaCollection('documents'));
    }

    protected function categoryOptions(): array
    {
        return [
            'Electricity',
            'Water',
            'Internet',
            'Rent',
            'Teacher Salaries',
            'Staff Salaries',
            'Transportation',
            'Maintenance & Repairs',
            'Teaching Supplies',
            'Administrative Supplies',
            'Software & Licenses',
            'Marketing & Events',
            'Miscellaneous',
        ];
    }
}
