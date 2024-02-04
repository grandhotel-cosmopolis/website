<?php

namespace Database\Seeders;

use App\Models\EventLocation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (config('app.env') === 'local') {
            EventLocation::factory()
                ->count(5)
                ->create();
        }
    }
}
