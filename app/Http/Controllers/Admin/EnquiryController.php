<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Enquiry;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    protected array $statuses = ['new', 'contacted', 'converted', 'lost'];

    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $statusFilter = $request->query('status');

        $enquiries = Enquiry::with(['classGroup', 'subject', 'assignee'])
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($statusFilter, fn ($query) => $query->where('status', $statusFilter))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.enquiries.index', [
            'enquiries' => $enquiries,
            'statuses' => $this->statuses,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function create(Request $request): View
    {
        [$classGroups, $subjects, $staff] = $this->referenceData($request);

        return view('admin.enquiries.create', [
            'enquiry' => new Enquiry(),
            'classGroups' => $classGroups,
            'subjects' => $subjects,
            'staff' => $staff,
            'statuses' => $this->statuses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $data = $this->validatedData($request);
        $data['tenant_id'] = $tenantId;

        Enquiry::create($data);

        return redirect()->route('admin.enquiries.index')
            ->with('status', 'Enquiry recorded successfully.');
    }

    public function edit(Request $request, Enquiry $enquiry): View
    {
        $this->assertTenant($request, $enquiry);
        [$classGroups, $subjects, $staff] = $this->referenceData($request);

        return view('admin.enquiries.edit', [
            'enquiry' => $enquiry,
            'classGroups' => $classGroups,
            'subjects' => $subjects,
            'staff' => $staff,
            'statuses' => $this->statuses,
        ]);
    }

    public function update(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $this->assertTenant($request, $enquiry);
        $data = $this->validatedData($request);

        $enquiry->update($data);

        return redirect()->route('admin.enquiries.index')
            ->with('status', 'Enquiry updated successfully.');
    }

    public function destroy(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $this->assertTenant($request, $enquiry);
        $enquiry->delete();

        return redirect()->route('admin.enquiries.index')
            ->with('status', 'Enquiry deleted.');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'class_group_id' => ['nullable', 'exists:class_groups,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'source' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:' . implode(',', $this->statuses)],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'follow_up_at' => ['nullable', 'date'],
            'closed_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
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

        $staff = User::role(['admin', 'manager', 'reception'])
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderBy('name')
            ->pluck('name', 'id');

        return [$classGroups, $subjects, $staff];
    }

    protected function assertTenant(Request $request, Enquiry $enquiry): void
    {
        $tenantId = $request->user()->tenant_id;
        if ($tenantId && $enquiry->tenant_id !== $tenantId) {
            abort(403);
        }
    }
}

