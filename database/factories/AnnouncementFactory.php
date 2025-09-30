<?php

namespace Database\Factories;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'title' => $this->faker->sentence(3),
            'body' => $this->faker->paragraph(),
            'published_at' => $this->faker->optional()->dateTimeBetween('-1 month', '+1 month'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
