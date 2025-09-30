<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\Student;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();

        if (!$tenant) {
            $tenant = Tenant::factory()->create(['slug' => 'default', 'name' => 'Default Academy']);
        }

        ClassGroup::where('tenant_id', $tenant->id)->get()->each(function (ClassGroup $class) use ($tenant) {
            $target = 20;
            $current = Student::where('tenant_id', $tenant->id)
                ->where('class_group_id', $class->id)
                ->count();
            $needed = max(0, $target - $current);

            if ($needed === 0) {
                return;
            }

            Student::factory()
                ->count($needed)
                ->create([
                    'tenant_id' => $tenant->id,
                    'class_group_id' => $class->id,
                ]);
        });
    }
}
