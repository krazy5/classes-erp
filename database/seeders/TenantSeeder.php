<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::firstOrCreate(
            ['slug' => 'default'],
            ['name' => 'Default Academy', 'settings' => [
                'timezone' => config('app.timezone'),
                'institute_name' => 'Default Academy',
                'academic_year' => now()->format('Y') . '-' . now()->addYear()->format('Y'),
                'institute_logo_url' => null,
                'institute_logo_path' => null,
                'institute_logo_disk' => 'public',
                'address' => '123 Learning Lane, Knowledge City',
                'payment_gateway' => [
                    'enabled' => false,
                    'provider' => null,
                    'meta' => [],
                ],
            ]]
        );
    }
}

