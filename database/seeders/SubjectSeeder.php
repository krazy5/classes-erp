<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();

        if (!$tenant) {
            $tenant = Tenant::factory()->create(['slug' => 'default', 'name' => 'Default Academy']);
        }

        $existing = Subject::where('tenant_id', $tenant->id)->count();

        if ($existing >= 6) {
            return;
        }

        Subject::factory()
            ->count(6 - $existing)
            ->create(['tenant_id' => $tenant->id]);
    }
}
