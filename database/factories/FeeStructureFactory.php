<?php

namespace Database\Factories;

use App\Models\FeeStructure;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeeStructureFactory extends Factory
{
    protected $model = FeeStructure::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'name' => $this->faker->randomElement([
                'Annual Tuition',
                'Laboratory Fee',
                'Coaching Package',
            ]),
            'description' => $this->faker->sentence(),
            'class_group_id' => null,
            'subject_id' => null,
            'amount' => $this->faker->randomFloat(2, 500, 5000),
            'frequency' => $this->faker->randomElement(['one_time', 'monthly', 'quarterly']),
            'effective_from' => now()->subMonths(rand(0, 3))->startOfMonth(),
            'effective_to' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
