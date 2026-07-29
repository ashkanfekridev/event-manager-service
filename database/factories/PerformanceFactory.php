<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Hall;
use App\Models\Performance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Performance>
 */
class PerformanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'hall_id' => Hall::factory(),
            'starts_at' => now()->addWeek(),
            'sales_start_at' => now()->subDay(),
            'sales_end_at' => now()->addDays(6),
            'status' => 'scheduled',
        ];
    }
}
