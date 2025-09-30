<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StudentGuardianController extends Controller
{
    public function edit(Request $request, Student $student, User $guardian): View
    {
        $this->assertTenant($request, $student);

        $relationship = $this->ensureGuardianRelationship($student, $guardian);

        return view('admin.students.guardians.edit', [
            'student' => $student,
            'guardian' => $guardian,
            'relationship' => $relationship->relationship_type,
        ]);
    }

    public function update(Request $request, Student $student, User $guardian): RedirectResponse
    {
        $this->assertTenant($request, $student);

        $relationship = $this->ensureGuardianRelationship($student, $guardian);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($guardian->id),
            ],
            'relationship_type' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', Password::defaults(), 'confirmed'],
        ]);

        $guardian->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($student->tenant_id && $guardian->tenant_id !== $student->tenant_id) {
            $guardian->tenant_id = $student->tenant_id;
        }

        if (!empty($validated['password'])) {
            $guardian->password = $validated['password'];
        }

        $guardian->save();
        $guardian->assignRole('parent');

        $student->user->guardians()->syncWithoutDetaching([
            $guardian->id => ['relationship_type' => $validated['relationship_type'] ?? null],
        ]);

        $relationship->relationship_type = $validated['relationship_type'] ?? null;
        $relationship->save();

        return redirect()
            ->route('admin.students.show', $student)
            ->with('status', 'Guardian updated successfully.');
    }

    protected function assertTenant(Request $request, Student $student): void
    {
        $tenantId = $request->user()->tenant_id;

        if ($tenantId && $student->tenant_id !== $tenantId) {
            abort(403);
        }
    }

    protected function ensureGuardianRelationship(Student $student, User $guardian): Pivot
    {
        $student->loadMissing('user.guardians');

        $studentUser = $student->user;

        abort_unless($studentUser, 404);

        $pivot = $studentUser->guardians->firstWhere('id', $guardian->id)?->pivot;

        abort_unless($pivot, 404);

        return $pivot;
    }
}
