<?php

namespace Database\Factories;

use App\Http\Controllers\Event\Recurrence;
use App\Models\RecurringEvent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringEvent>
 */
class RecurringEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = Carbon::now();
        return [
            'guid' => uuid_create(),
            'default_title_de' => fake()->realText(40),
            'default_title_en' => fake()->realText(40),
            'default_description_de' => fake()->realText(),
            'default_description_en' => fake()->realText(),
            'recurrence' => Recurrence::EVERY_X_DAYS,
            'recurrence_metadata' => 14,
            'start_first_occurrence' => $start,
            'end_first_occurrence' => $start->addHours(2)
        ];
    }
}
