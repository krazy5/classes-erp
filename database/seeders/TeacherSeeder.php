<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();

        if (!$tenant) {
            $tenant = Tenant::factory()->create(['slug' => 'default', 'name' => 'Default Academy']);
        }

        // Ensure that every teacher user has a teacher profile
        User::role('teacher')->get()->each(function (User $user) use ($tenant) {
            Teacher::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'tenant_id' => $tenant->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'dob' => $user->date_of_birth,
                ]
            );
        });

        $desiredTotal = 6;
        $existing = Teacher::where('tenant_id', $tenant->id)->count();
        $additional = max(0, $desiredTotal - $existing);

        if ($additional === 0) {
            return;
        }

        for ($i = 0; $i < $additional; $i++) {
            $user = User::factory()->create(['tenant_id' => $tenant->id]);

            $dob = Carbon::parse($user->date_of_birth ?? now()->subYears(28));
            $user->forceFill([
                'date_of_birth' => $dob->toDateString(),
                'password' => Hash::make($dob->format('dmY')),
            ])->save();
            $user->assignRole('teacher');

            Teacher::factory()
                ->for($user)
                ->create([
                    'tenant_id' => $tenant->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'dob' => $user->date_of_birth,
                ]);
        }
    }
}
