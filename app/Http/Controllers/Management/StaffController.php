<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StaffController extends Controller
{
    private const MANAGED_ROLES = ['manager', 'reception'];

    public function index(Request $request): View
    {
        $roles = $this->availableRoles($request->user());

        if (empty($roles)) {
            abort(403);
        }

        $tenantId = $request->user()->tenant_id;
        $search = trim((string) $request->input('search'));

        $staff = User::role($roles)
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

        return view('management.staff.index', compact('staff', 'search', 'roles'));
    }

    public function create(Request $request): View
    {
        $roles = $this->availableRoles($request->user());

        if (empty($roles)) {
            abort(403);
        }

        return view('management.staff.create', [
            'staff' => new User(),
            'roles' => $roles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $tenantId = $request->user()->tenant_id;
        $dob = Carbon::parse($data['date_of_birth']);
        $password = $data['password'] ?? null;
        $role = $data['role'];
        unset($data['password'], $data['role']);

        DB::transaction(function () use ($data, $tenantId, $dob, $password, $role) {
            $user = User::create([
                'tenant_id' => $tenantId,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $password ?? $this->defaultPasswordFromDate($dob),
                'date_of_birth' => $dob->toDateString(),
            ]);

            $user->syncRoles([$role]);
        });

        return redirect()->route('management.staff.index')
            ->with('status', 'Staff account created successfully.');
    }

    public function edit(Request $request, User $staff): View
    {
        $this->assertManageable($request, $staff);

        return view('management.staff.edit', [
            'staff' => $staff,
            'roles' => $this->availableRoles($request->user()),
        ]);
    }

    public function update(Request $request, User $staff): RedirectResponse
    {
        $this->assertManageable($request, $staff);

        $data = $this->validatedData($request, $staff);
        $dob = Carbon::parse($data['date_of_birth']);
        $password = $data['password'] ?? null;
        $role = $data['role'];
        unset($data['password'], $data['role']);

        DB::transaction(function () use ($staff, $data, $dob, $password, $role) {
            $staff->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'date_of_birth' => $dob->toDateString(),
            ])->save();

            if ($password) {
                $staff->password = $password;
                $staff->save();
            }

            $staff->syncRoles([$role]);
        });

        return redirect()->route('management.staff.index')
            ->with('status', 'Staff account updated successfully.');
    }

    public function destroy(Request $request, User $staff): RedirectResponse
    {
        $this->assertManageable($request, $staff);

        $staff->delete();

        return redirect()->route('management.staff.index')
            ->with('status', 'Staff account removed.');
    }

    protected function validatedData(Request $request, ?User $staff = null): array
    {
        $roles = $this->availableRoles($request->user());

        if (empty($roles)) {
            abort(403);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($staff?->id),
            ],
            'date_of_birth' => ['required', 'date'],
            'role' => ['required', Rule::in($roles)],
            'password' => ['nullable', 'string', Password::defaults()],
        ]);
    }

    protected function assertManageable(Request $request, User $staff): void
    {
        $tenantId = $request->user()->tenant_id;
        $roles = $this->availableRoles($request->user());

        if (!$staff->hasAnyRole(self::MANAGED_ROLES)) {
            abort(404);
        }

        if ($tenantId && $staff->tenant_id !== $tenantId) {
            abort(403);
        }

        if (!$staff->roles->pluck('name')->intersect($roles)->isNotEmpty()) {
            abort(403);
        }
    }

    protected function availableRoles(User $user): array
    {
        if ($user->hasRole('admin')) {
            return self::MANAGED_ROLES;
        }

        if ($user->hasRole('manager')) {
            return ['reception'];
        }

        return [];
    }

    protected function defaultPasswordFromDate(Carbon $date): string
    {
        return $date->format('dmY');
    }
}
