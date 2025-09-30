<?php

namespace Database\Factories;

use App\Models\FeeRecord;
use App\Models\Installment;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstallmentFactory extends Factory
{
    protected $model = Installment::class;

    public function definition(): array
    {
        $paidDate = $this->faker->optional()->dateTimeBetween('-1 month', 'now');
        $amount = $this->faker->randomFloat(2, 100, 1000);
        $paidAmount = $paidDate
            ? $this->faker->randomFloat(2, $amount * 0.5, $amount)
            : 0;

        return [
            'tenant_id' => null,
            'fee_record_id' => FeeRecord::factory(),
            'sequence' => null,
            'amount' => $amount,
            'paid_amount' => min($paidAmount, $amount),
            'due_date' => $this->faker->dateTimeBetween('now', '+3 months'),
            'paid_at' => $paidDate,
            'payment_method' => $paidDate ? $this->faker->randomElement(['cash', 'bank transfer', 'card']) : null,
            'reference' => $paidDate ? strtoupper($this->faker->bothify('REF###??')) : null,
            'receipt_number' => $paidDate ? strtoupper($this->faker->bothify('RCPT#####')) : null,
            'receipt_issued_at' => $paidDate ?: null,
            'remarks' => $this->faker->optional()->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
