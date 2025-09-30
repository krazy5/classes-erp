<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'default'],
            ['name' => 'Default Academy']
        );

        $classGroup = ClassGroup::where('tenant_id', $tenant->id)->first();

        if (!$classGroup) {
            $classGroup = ClassGroup::factory()->create([
                'tenant_id' => $tenant->id,
                'name' => '10th A',
            ]);
        }

        $demoUsers = [
            [
                'name' => 'Mohsin Admin',
                'email' => 'mohsin.mohsin6@gmail.com',
                'dob' => '1985-01-01',
                'roles' => ['admin'],
            ],
            [
                'name' => 'System Admin',
                'email' => 'admin@classerp.test',
                'dob' => '1988-05-15',
                'roles' => ['admin'],
            ],
            [
                'name' => 'Operations Manager',
                'email' => 'manager@classerp.test',
                'dob' => '1990-03-22',
                'roles' => ['manager'],
            ],
            [
                'name' => 'Lead Teacher',
                'email' => 'teacher@classerp.test',
                'dob' => '1992-07-18',
                'roles' => ['teacher'],
                'ensure_teacher_profile' => true,
            ],
            [
                'name' => 'Front Desk Reception',
                'email' => 'reception@classerp.test',
                'dob' => '1995-09-09',
                'roles' => ['reception', 'student'],
                'student_profile' => [
                    'class_group_id' => $classGroup->id,
                    'dob' => '1995-09-09',
                ],
            ],
            [
                'name' => 'Demo Student',
                'email' => 'student@classerp.test',
                'dob' => '2009-02-11',
                'roles' => ['student'],
                'student_profile' => [
                    'class_group_id' => $classGroup->id,
                    'dob' => '2009-02-11',
                ],
            ],
            [
                'name' => 'Guardian Parent',
                'email' => 'parent@classerp.test',
                'dob' => '1980-08-08',
                'roles' => ['parent'],
                'guardian_of' => 'student@classerp.test',
            ],
        ];

        $createdUsers = [];
        $guardianLinks = [];

        foreach ($demoUsers as $data) {
            $dob = Carbon::parse($data['dob'] ?? now()->subYears(20));
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'tenant_id' => $tenant->id,
                    'name' => $data['name'],
                    'password' => Hash::make($dob->format('dmY')),
                    'date_of_birth' => $dob->toDateString(),
                ]
            );

            $user->syncRoles($data['roles']);

            if (!empty($data['ensure_teacher_profile'])) {
                Teacher::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'tenant_id' => $tenant->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ]
                );
            }

            if (!empty($data['student_profile'])) {
                $profile = $data['student_profile'];
                $profileDob = Carbon::parse($profile['dob'] ?? $dob->toDateString());

                Student::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'tenant_id' => $tenant->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $profile['phone'] ?? '9999999999',
                        'dob' => $profileDob->toDateString(),
                        'gender' => $profile['gender'] ?? 'other',
                        'address' => $profile['address'] ?? 'Demo Address',
                        'class_group_id' => $profile['class_group_id'] ?? $classGroup->id,
                    ]
                );
            }

            if (!empty($data['guardian_of'])) {
                $guardianLinks[] = [
                    'guardian' => $user,
                    'student_email' => $data['guardian_of'],
                    'relationship' => $data['relationship_type'] ?? 'parent',
                ];
            }

            $createdUsers[$data['email']] = $user;
        }

        foreach ($guardianLinks as $link) {
            $studentUser = $createdUsers[$link['student_email']] ?? User::where('email', $link['student_email'])->first();

            if ($studentUser) {
                $link['guardian']->students()->syncWithoutDetaching([
                    $studentUser->id => ['relationship_type' => $link['relationship']],
                ]);
            }
        }
    }
}
