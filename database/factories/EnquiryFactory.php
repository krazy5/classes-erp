<?php

namespace Database\Factories;

use App\Models\Enquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnquiryFactory extends Factory
{
    protected $model = Enquiry::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->optional()->safeEmail(),
            'class_group_id' => null,
            'subject_id' => null,
            'source' => $this->faker->randomElement(['walk-in', 'referral', 'website', 'phone']),
            'status' => $this->faker->randomElement(['new', 'contacted', 'converted', 'lost']),
            'assigned_to' => null,
            'follow_up_at' => $this->faker->optional()->dateTimeBetween('now', '+7 days'),
            'closed_at' => null,
            'notes' => $this->faker->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
