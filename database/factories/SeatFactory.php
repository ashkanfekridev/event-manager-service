<?php

namespace Database\Factories;

use App\Models\Hall;
use App\Models\Seat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Seat>
 */
class SeatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hall_id' => Hall::factory(),
            'section' => 'main',
            'row_label' => fake()->randomElement(['A', 'B', 'C']),
            'number' => (string) fake()->numberBetween(1, 100),
            'code' => fake()->unique()->bothify('main-?-##'),
            'type' => 'standard',
            'is_active' => true,
        ];
    }
}
