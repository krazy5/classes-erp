<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.view.admin',
            'dashboard.view.manager',
            'dashboard.view.teacher',
            'dashboard.view.reception',
            'dashboard.view.student',
            'dashboard.view.parent',
            'timetable.view.assigned',
            'timetable.view.student',
            'attendance.record.class',
            'attendance.view.student',
            'fees.view.student',
            'enquiry.manage',
            'profile.view.reception',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $rolePermissions = [
            'admin' => $permissions,
            'manager' => [
                'dashboard.view.manager',
                'timetable.view.assigned',
                'attendance.view.student',
                'fees.view.student',
                'enquiry.manage',
            ],
            'teacher' => [
                'dashboard.view.teacher',
                'timetable.view.assigned',
                'attendance.record.class',
                'attendance.view.student',
            ],
            'reception' => [
                'dashboard.view.reception',
                'profile.view.reception',
                'enquiry.manage',
                'attendance.record.class',
                'attendance.view.student',
            ],
            'student' => [
                'dashboard.view.student',
                'timetable.view.student',
                'attendance.view.student',
                'fees.view.student',
            ],
            'parent' => [
                'dashboard.view.parent',
                'timetable.view.student',
                'attendance.view.student',
                'fees.view.student',
            ],
        ];

        foreach ($rolePermissions as $role => $assignedPermissions) {
            $roleModel = Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);

            if ($role === 'admin') {
                $roleModel->syncPermissions(Permission::all());

                continue;
            }

            $roleModel->syncPermissions($assignedPermissions);
        }
    }
}
