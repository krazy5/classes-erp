<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ClassGroupSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();

        if (!$tenant) {
            $tenant = Tenant::factory()->create(['slug' => 'default', 'name' => 'Default Academy']);
        }

        $desired = 3;
        $existing = ClassGroup::where('tenant_id', $tenant->id)->count();
        $needed = max(0, $desired - $existing);

        if ($needed === 0) {
            return;
        }

        ClassGroup::factory()
            ->count($needed)
            ->create(['tenant_id' => $tenant->id]);
    }
}
