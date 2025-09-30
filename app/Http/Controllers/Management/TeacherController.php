<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $search = trim((string) $request->input('search'));

        $teachers = Teacher::with('user')
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('management.teachers.index', compact('teachers', 'search'));
    }

    public function create(Request $request): View
    {
        return view('management.teachers.create', [
            'teacher' => new Teacher(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $tenantId = $request->user()->tenant_id;
        $dob = Carbon::parse($data['dob']);
        $password = $data['password'] ?? null;
        unset($data['password']);

        $teacher = DB::transaction(function () use ($data, $tenantId, $dob, $password) {
            $user = User::create([
                'tenant_id' => $tenantId,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $password ?? $this->defaultPasswordFromDate($dob),
                'date_of_birth' => $dob->toDateString(),
            ]);

            $user->assignRole('teacher');

            $attributes = $data;

            if ($tenantId && Schema::hasColumn((new Teacher())->getTable(), 'tenant_id')) {
                $attributes['tenant_id'] = $tenantId;
            }

            $attributes['user_id'] = $user->id;
            $attributes['dob'] = $dob->toDateString();

            return Teacher::create($attributes);
        });

        if ($request->hasFile('photo')) {
            $teacher->addMediaFromRequest('photo')->toMediaCollection('photo');
        }

        return redirect()->route('management.teachers.index')
            ->with('status', 'Teacher created successfully.');
    }

    public function edit(Request $request, Teacher $teacher): View
    {
        $this->assertTenant($request, $teacher);

        return view('management.teachers.edit', [
            'teacher' => $teacher->loadMissing('media'),
        ]);
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $this->assertTenant($request, $teacher);

        $data = $this->validatedData($request, $teacher);
        $dob = Carbon::parse($data['dob']);
        $tenantId = $request->user()->tenant_id;
        $password = $data['password'] ?? null;
        unset($data['password']);

        DB::transaction(function () use ($teacher, $data, $dob, $tenantId, $password) {
            if ($teacher->user) {
                $teacher->user->fill([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'date_of_birth' => $dob->toDateString(),
                ])->save();

                if ($password) {
                    $teacher->user->password = $password;
                    $teacher->user->save();
                }

                $teacher->user->assignRole('teacher');

                $teacher->update(array_merge($data, [
                    'user_id' => $teacher->user->id,
                    'dob' => $dob->toDateString(),
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

            $user->assignRole('teacher');

            $teacher->update(array_merge($data, [
                'user_id' => $user->id,
                'dob' => $dob->toDateString(),
            ]));
        });

        if ($request->boolean('remove_photo')) {
            $teacher->clearMediaCollection('photo');
        }

        if ($request->hasFile('photo')) {
            $teacher->addMediaFromRequest('photo')->toMediaCollection('photo');
        }

        return redirect()->route('management.teachers.index')
            ->with('status', 'Teacher updated successfully.');
    }

    public function destroy(Request $request, Teacher $teacher): RedirectResponse
    {
        $this->assertTenant($request, $teacher);

        $teacher->delete();

        return redirect()->route('management.teachers.index')
            ->with('status', 'Teacher removed.');
    }

    protected function validatedData(Request $request, ?Teacher $teacher = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('teachers', 'email')->ignore($teacher?->id),
                Rule::unique('users', 'email')->ignore($teacher?->user_id),
            ],
            'dob' => ['required', 'date'],
            'password' => ['nullable', 'string', Password::defaults()],
            'photo' => ['nullable', 'image', 'max:5120'],
            'remove_photo' => ['sometimes', 'boolean'],
        ]);

        unset($data['photo'], $data['remove_photo']);

        return $data;
    }

    protected function assertTenant(Request $request, Teacher $teacher): void
    {
        $tenantId = $request->user()->tenant_id;

        if ($tenantId && $teacher->tenant_id !== $tenantId) {
            abort(403);
        }
    }

    protected function defaultPasswordFromDate(Carbon $date): string
    {
        return $date->format('dmY');
    }
}
