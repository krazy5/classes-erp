<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;



class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $search = trim((string) $request->input('search'));

        $students = Student::with(['classGroup', 'media'])
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.students.index', compact('students', 'search'));
    }

    public function create(Request $request): View
    {
        return view('admin.students.create', [
            'student' => new Student(),
            'classGroups' => $this->classGroups($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $data = $this->validatedData($request);
        $dob = Carbon::parse($data['dob']);
        $password = $data['password'] ?? null;
        unset($data['password']);

        $student = DB::transaction(function () use ($data, $tenantId, $dob, $password) {
            $user = User::create([
                'tenant_id' => $tenantId,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $password ?? $this->defaultPasswordFromDate($dob),
                'date_of_birth' => $dob->toDateString(),
            ]);

            $user->assignRole('student');

            $attributes = $data;

            if ($tenantId && Schema::hasColumn((new Student())->getTable(), 'tenant_id')) {
                $attributes['tenant_id'] = $tenantId;
            }

            $attributes['user_id'] = $user->id;

            return Student::create($attributes);
        });

        if ($request->hasFile('photo')) {
            $student->addMediaFromRequest('photo')->toMediaCollection('photo');
        }

        return redirect()->route('admin.students.add-guardian', $student)
            ->with('status', 'Step 1 of 4: Student profile created. Now, add guardian details.');

        // return redirect()->route('admin.students.index')
        //     ->with('status', 'Student created successfully.');
    }

    public function show(Request $request, Student $student): View
    {
        $this->assertTenant($request, $student);

        return view('admin.students.show', [
            'student' => $student->loadMissing([
                'classGroup',
                'media',
                'user.guardians',
            ]),
        ]);
    }

    public function payments(Request $request, Student $student): View
    {
        $this->assertTenant($request, $student);

        $payments = $student->feeRecords()
            ->with([
                'feeStructure',
                'installments' => fn ($relation) => $relation->orderBy('sequence'),
            ])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.students.payments', [
            'student' => $student->loadMissing(['classGroup', 'user.guardians']),
            'payments' => $payments,
        ]);
    }

    public function edit(Request $request, Student $student): View
    {
        $this->assertTenant($request, $student);

        return view('admin.students.edit', [
            'student' => $student->loadMissing('media'),
            'classGroups' => $this->classGroups($request),
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $this->assertTenant($request, $student);

        $data = $this->validatedData($request, $student);
        $dob = Carbon::parse($data['dob']);
        $tenantId = $request->user()->tenant_id;
        $password = $data['password'] ?? null;
        unset($data['password']);

        DB::transaction(function () use ($student, $data, $dob, $tenantId, $password) {
            if ($student->user) {
                $student->user->fill([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'date_of_birth' => $dob->toDateString(),
                ])->save();

                if ($password) {
                    $student->user->password = $password;
                    $student->user->save();
                }

                $student->user->assignRole('student');

                $student->update(array_merge($data, [
                    'user_id' => $student->user->id,
                ]));

                return;
            }

            $user = User::create([
                'tenant_id' => $tenantId,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $password ?? $this->defaultPasswordFromDate($dob),
                'date_of_birth' => $dob->toDateString(),
            ]);

            $user->assignRole('student');

            $student->update(array_merge($data, [
                'user_id' => $user->id,
            ]));
        });

        if ($request->boolean('remove_photo')) {
            $student->clearMediaCollection('photo');
        }

        if ($request->hasFile('photo')) {
            $student->addMediaFromRequest('photo')->toMediaCollection('photo');
        }

        return redirect()->route('admin.students.index')
            ->with('status', 'Student updated successfully.');
    }

    public function destroy(Request $request, Student $student): RedirectResponse
    {
        $this->assertTenant($request, $student);

        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('status', 'Student removed.');
    }

    protected function validatedData(Request $request, ?Student $student = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('students', 'email')->ignore($student?->id),
                Rule::unique('users', 'email')->ignore($student?->user_id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'dob' => ['required', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'address' => ['nullable', 'string', 'max:500'],
            'class_group_id' => ['required', 'exists:class_groups,id'],
            'password' => ['nullable', 'string', Password::defaults()],
            'photo' => ['nullable', 'image', 'max:5120'],
            'remove_photo' => ['sometimes', 'boolean'],
        ], [], [
            'class_group_id' => 'class group',
        ]);

        unset($data['photo'], $data['remove_photo']);

        return $data;
    }

    protected function classGroups(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        return ClassGroup::when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    protected function assertTenant(Request $request, Student $student): void
    {
        $tenantId = $request->user()->tenant_id;

        if ($tenantId && $student->tenant_id !== $tenantId) {
            abort(403);
        }
    }

    protected function defaultPasswordFromDate(Carbon $date): string
    {
        return $date->format('dmY');
    }



    // In Controllers/StudentController.php (add these methods at the end of the class)

    /**
     * Show the form for adding a guardian to a student (Wizard Step 2).
     */
  
    public function addGuardianForm(Request $request, Student $student): View
    {
        $this->assertTenant($request, $student);

        $existingGuardians = \App\Models\User::role('parent')
            ->when($request->user()->tenant_id, fn ($q, $tid) => $q->where('tenant_id', $tid))
            ->orderBy('name')
            ->get(['id','name','email']);

        return view('admin.students.add-guardian', compact('student', 'existingGuardians'));
    }

    /**
     * Store the guardian information and link to the student.
     */
    // In StudentController.php

    /**
     * Store the guardian information and link to the student.
     */
   public function storeGuardian(Request $request, Student $student)
{
    $this->assertTenant($request, $student);

    $type = $request->input('guardian_type', 'new');

    $rules = [
        'guardian_type' => ['required', 'in:new,existing'],
        'relationship_type' => ['nullable', 'string', 'max:100'],
        'password' => ['nullable', Password::defaults(), 'confirmed'],
    ];

    if ($type === 'existing') {
        $rules['existing_guardian_id'] = ['required', 'integer', 'exists:users,id'];
    } else { // new
        $rules['name']  = ['required', 'string', 'max:255'];
        $rules['email'] = ['required', 'email', 'max:255', Rule::unique('users', 'email')];
    }

    $validated = $request->validate($rules);

    $student->loadMissing('user');
    if (!$student->user) {
        return back()->withErrors(['error' => 'Could not find the user profile for this student.']);
    }

        DB::transaction(function () use ($request, $student, $type) {
            $tenantId = $request->user()->tenant_id;

            if ($type === 'existing') {
                $guardianUser = User::find($request->integer('existing_guardian_id'));
                if ($request->filled('password')) {
                    $guardianUser->password = Hash::make($request->input('password'));
                    $guardianUser->save();
                }
            } else {
            $raw = $request->filled('password')
                ? $request->input('password')
                : $request->string('email')->toString(); // default = email

            $guardianUser = User::create([
                'tenant_id' => $tenantId,
                'name'      => $request->string('name'),
                'email'     => $request->string('email'),
                'password'  => Hash::make($raw),
            ]);
        }

        if ($tenantId && $guardianUser->tenant_id !== $tenantId) {
            $guardianUser->tenant_id = $tenantId;
        }

        $guardianUser->assignRole('parent');

        $guardianUser->save();

        $student->user->guardians()->syncWithoutDetaching([
            $guardianUser->id => ['relationship_type' => $request->input('relationship_type')],
        ]);
    });

    return redirect()
        ->route('admin.students.documents', ['student' => $student, 'onboarding' => 1])
        ->with('status', 'Step 2 of 4: Guardian added successfully. Next, upload admission documents.');
}

    /**
     * Show the form for adding a fee plan to a student (Wizard Step 4).
     */
    public function addFeePlanForm(Request $request, Student $student): View
    {
        $this->assertTenant($request, $student);
        $tenantId = $request->user()->tenant_id;

        $feeStructures = \App\Models\FeeStructure::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where(function ($query) use ($student) {
                // Show structures for the student's class group OR global structures
                $query->where('class_group_id', $student->class_group_id)
                    ->orWhereNull('class_group_id');
            })
            ->pluck('name', 'id');

        return view('admin.students.add-fee-plan', compact('student', 'feeStructures'));
    }

    /**
     * Store the fee plan for the student.
     */
    public function storeFeePlan(Request $request, Student $student): RedirectResponse
    {
        $this->assertTenant($request, $student);

        $data = $request->validate([
            'fee_structure_id' => ['required', 'exists:fee_structures,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $feeStructure = \App\Models\FeeStructure::find($data['fee_structure_id']);

        DB::transaction(function () use ($student, $feeStructure, $data) {
            $feeRecord = $student->feeRecords()->create([
                'tenant_id' => $student->tenant_id,
                'fee_structure_id' => $feeStructure->id,
                'total_amount' => $feeStructure->amount,
                'notes' => $data['notes'],
            ]);

            // Here you would add logic to auto-generate installments based on frequency
            // For now, let's create a single installment for the full amount
            $feeRecord->installments()->create([
                'tenant_id' => $student->tenant_id,
                'amount' => $feeStructure->amount,
                'due_date' => now()->addDays(7), // Example: due in 7 days
            ]);
        });

        // The wizard is complete! Redirect to the student's profile or list.
        return redirect()->route('admin.students.index')
            ->with('status', 'Step 4 of 4: Fee plan assigned. Admission complete!');
    }

}

