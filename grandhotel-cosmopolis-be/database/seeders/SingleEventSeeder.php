<?php

namespace Database\Seeders;

use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\SingleEvent;
use App\Models\User;
use Database\Factories\UserFactory;
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
            ->for(
                FileUpload::factory()->for(
                    User::factory()->create(),
                    'uploadedBy'
                )->create())
            ->for(User::factory()->create(), 'createdBy')
            ->create();
    }
}
