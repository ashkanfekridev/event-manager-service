<?php

namespace Database\Factories;

use App\Models\Performance;
use App\Models\PerformanceSeat;
use App\Models\Seat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PerformanceSeat>
 */
class PerformanceSeatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'performance_id' => Performance::factory(),
            'seat_id' => Seat::factory(),
            'price' => fake()->numberBetween(100000, 2000000),
            'status' => 'available',
        ];
    }
}
