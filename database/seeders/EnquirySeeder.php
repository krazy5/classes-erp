<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\Enquiry;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class EnquirySeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();

        if (!$tenant) {
            return;
        }

        $classGroups = ClassGroup::where('tenant_id', $tenant->id)->get();

        if ($classGroups->isEmpty()) {
            return;
        }

        Enquiry::factory()
            ->count(8)
            ->create(['tenant_id' => $tenant->id])
            ->each(function (Enquiry $enquiry) use ($classGroups) {
                $group = $classGroups->random();
                $enquiry->update([
                    'class_group_id' => $group->id,
                ]);
            });
    }
}
