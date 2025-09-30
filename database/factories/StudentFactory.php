<?php

namespace Database\Factories;

use App\Models\ClassGroup;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'user_id' => null, // or link to User::factory() if needed
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'dob' => $this->faker->date(),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'address' => $this->faker->address(),
            'class_group_id' => ClassGroup::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
