<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\FeeStructure;
use App\Models\Subject;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FeeStructureController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $feeStructures = FeeStructure::with(['classGroup', 'subject'])
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderBy('name')
            ->paginate(15);

        return view('admin.fee-structures.index', compact('feeStructures'));
    }

    public function create(Request $request): View
    {
        [$classGroups, $subjects] = $this->referenceData($request);

        return view('admin.fee-structures.create', [
            'feeStructure' => new FeeStructure(),
            'classGroups' => $classGroups,
            'subjects' => $subjects,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $data = $this->validatedData($request);
        $data['tenant_id'] = $tenantId;
        $data['is_active'] = $request->boolean('is_active', true);

        FeeStructure::create($data);

        return redirect()->route('admin.fee-structures.index')
            ->with('status', 'Fee structure created successfully.');
    }

    public function edit(Request $request, FeeStructure $feeStructure): View
    {
        $this->assertTenant($request, $feeStructure);
        [$classGroups, $subjects] = $this->referenceData($request);

        return view('admin.fee-structures.edit', [
            'feeStructure' => $feeStructure,
            'classGroups' => $classGroups,
            'subjects' => $subjects,
        ]);
    }

    public function update(Request $request, FeeStructure $feeStructure): RedirectResponse
    {
        $this->assertTenant($request, $feeStructure);
        $data = $this->validatedData($request);
        $data['is_active'] = $request->boolean('is_active', true);

        $feeStructure->update($data);

        return redirect()->route('admin.fee-structures.index')
            ->with('status', 'Fee structure updated successfully.');
    }

    public function destroy(Request $request, FeeStructure $feeStructure): RedirectResponse
    {
        $this->assertTenant($request, $feeStructure);
        $feeStructure->delete();

        return redirect()->route('admin.fee-structures.index')
            ->with('status', 'Fee structure removed.');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'class_group_id' => ['nullable', 'exists:class_groups,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'frequency' => ['required', 'in:one_time,monthly,quarterly'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_active' => ['nullable', 'boolean'],
        ], [], [
            'class_group_id' => 'class group',
        ]);
    }

    protected function referenceData(Request $request): array
    {
        $tenantId = $request->user()->tenant_id;
        $classGroups = ClassGroup::when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderBy('name')
            ->pluck('name', 'id');

        $subjects = Subject::when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderBy('name')
            ->pluck('name', 'id');

        return [$classGroups, $subjects];
    }

    protected function assertTenant(Request $request, FeeStructure $feeStructure): void
    {
        $tenantId = $request->user()->tenant_id;
        if ($tenantId && $feeStructure->tenant_id !== $tenantId) {
            abort(403);
        }
    }
}
