<?php

namespace Database\Factories;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'user_id' => null,
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail(),
            'dob' => $this->faker->date(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
