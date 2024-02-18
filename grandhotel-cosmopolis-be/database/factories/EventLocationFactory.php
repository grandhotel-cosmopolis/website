<?php

namespace Database\Factories;

use App\Models\EventLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventLocation>
 */
class EventLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'guid' => uuid_create(),
            'name' => fake()->name(),
            'street' => fake()->streetName(),
            'city' => fake()->city(),
            'additional_information' => fake()->word()
        ];
    }
}
