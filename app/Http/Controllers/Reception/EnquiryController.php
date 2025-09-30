<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EnquiryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->can('enquiry.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'class_group_id' => ['nullable', Rule::exists('class_groups', 'id')],
            'subject_id' => ['nullable', Rule::exists('subjects', 'id')],
            'source' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [], [
            'class_group_id' => 'class group',
            'subject_id' => 'subject',
        ]);

        Enquiry::create([
            'tenant_id' => $user->tenant_id,
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'class_group_id' => $data['class_group_id'] ?? null,
            'subject_id' => $data['subject_id'] ?? null,
            'source' => $data['source'] ?? 'walk-in',
            'status' => 'new',
            'assigned_to' => $user->id,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('reception.dashboard')
            ->with('status', 'Enquiry recorded successfully.');
    }
}
