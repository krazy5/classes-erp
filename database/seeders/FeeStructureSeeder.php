<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\FeeStructure;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class FeeStructureSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();

        if (!$tenant) {
            return;
        }

        ClassGroup::where('tenant_id', $tenant->id)->get()->each(function (ClassGroup $classGroup) use ($tenant) {
            FeeStructure::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'class_group_id' => $classGroup->id,
                    'name' => $classGroup->name . ' Tuition',
                ],
                [
                    'amount' => 2500,
                    'frequency' => 'monthly',
                    'is_active' => true,
                ]
            );
        });

        // Generic admission fee if not present
        FeeStructure::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'class_group_id' => null,
                'name' => 'Admission Fee',
            ],
            [
                'amount' => 1500,
                'frequency' => 'one_time',
                'is_active' => true,
            ]
        );
    }
}
