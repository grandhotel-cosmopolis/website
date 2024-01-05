<?php

namespace Database\Seeders;

use App\Models\EventLocation;
use App\Models\SingleEvent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SingleEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SingleEvent::factory()
            ->count(5)
            ->for(EventLocation::factory()->create())
            ->create();
    }
}
