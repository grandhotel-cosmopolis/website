<?php

namespace Database\Factories;

use App\Models\SingleEvent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SingleEvent>
 */
class SingleEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = Carbon::now()->addDays(fake()->numberBetween(3, 40));
        return [
            'guid' => uuid_create(),
            'title_de' => fake()->realText(40),
            'title_en' => fake()->realText(40),
            'description_de' => fake()->realText(),
            'description_en' => fake()->realText(),
            'start' => $start,
            'end' => $start->addHours(2),
            'is_recurring' => false
        ];
    }
}
