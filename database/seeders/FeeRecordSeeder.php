<?php

namespace Database\Seeders;

use App\Models\FeeRecord;
use App\Models\FeeStructure;
use App\Models\Installment;
use App\Models\Student;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class FeeRecordSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();

        if (!$tenant) {
            return;
        }

        $feeStructures = FeeStructure::where('tenant_id', $tenant->id)->get();

        Student::where('tenant_id', $tenant->id)->with('classGroup')->get()->each(function (Student $student) use ($tenant, $feeStructures) {
            $structure = $feeStructures->firstWhere('class_group_id', optional($student->classGroup)->id)
                ?? $feeStructures->firstWhere('name', 'Admission Fee')
                ?? $feeStructures->first();

            if (!$structure) {
                return;
            }

            $fee = FeeRecord::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'student_id' => $student->id,
                ],
                FeeRecord::factory()->raw([
                    'tenant_id' => $tenant->id,
                    'student_id' => $student->id,
                    'fee_structure_id' => $structure->id,
                    'total_amount' => $structure->amount,
                ])
            );

            if (!$fee->fee_structure_id) {
                $fee->forceFill([
                    'fee_structure_id' => $structure->id,
                    'total_amount' => $structure->amount,
                ])->save();
            }

            if ($fee->installments()->exists()) {
                return;
            }

            $installmentAmount = round($structure->amount / 3, 2);

            Installment::factory()
                ->count(3)
                ->create([
                    'tenant_id' => $tenant->id,
                    'fee_record_id' => $fee->id,
                    'amount' => $installmentAmount,
                ]);

            $fee->refresh();
            $fee->refreshPaymentStatus();
        });
    }
}
