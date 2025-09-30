<?php

namespace Database\Factories;

use App\Models\ClassGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassGroupFactory extends Factory
{
    protected $model = ClassGroup::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'name' => $this->faker->unique()->randomElement([
                '10th A', '10th B', '12th Science', '12th Commerce', '11th Arts'
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
