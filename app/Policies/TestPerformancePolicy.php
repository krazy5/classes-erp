<?php

namespace App\Policies;

use App\Models\TestPerformance;
use App\Models\User;

class TestPerformancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager', 'teacher', 'reception', 'student', 'parent']);
    }

    public function view(User $user, TestPerformance $performance): bool
    {
        if ($user->hasAnyRole(['admin', 'manager', 'teacher', 'reception'])) {
            return $this->matchesTenant($user, $performance);
        }

        if ($user->hasRole('student')) {
            return $performance->student?->user_id === $user->id;
        }

        if ($user->hasRole('parent')) {
            return $user->wards()->where('students.id', $performance->student_id)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager', 'teacher', 'reception']);
    }

    public function update(User $user, TestPerformance $performance): bool
    {
        if (!$this->matchesTenant($user, $performance)) {
            return false;
        }

        return $user->hasAnyRole(['admin', 'manager', 'teacher', 'reception']);
    }

    public function delete(User $user, TestPerformance $performance): bool
    {
        if (!$this->matchesTenant($user, $performance)) {
            return false;
        }

        return $user->hasAnyRole(['admin', 'manager', 'teacher']);
    }

    public function download(User $user, TestPerformance $performance): bool
    {
        return $this->view($user, $performance);
    }

    protected function matchesTenant(User $user, TestPerformance $performance): bool
    {
        if (!$user->tenant_id || !$performance->tenant_id) {
            return true;
        }

        return (int) $user->tenant_id === (int) $performance->tenant_id;
    }
}
