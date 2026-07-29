<?php

namespace Database\Factories;

use App\Models\Hall;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hall>
 */
class HallFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'venue_id' => Venue::factory(),
            'name' => 'Hall '.fake()->unique()->numberBetween(1, 9999),
            'capacity' => 0,
        ];
    }
}
