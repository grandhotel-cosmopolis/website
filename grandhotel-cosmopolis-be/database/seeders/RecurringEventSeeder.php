<?php

namespace Database\Seeders;

use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\RecurringEvent;
use App\Models\SingleEvent;
use App\Models\User;
use Illuminate\Database\Seeder;

class RecurringEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::factory()->create();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        RecurringEvent::factory()
            ->count(5)
            ->for($eventLocation, 'defaultEventLocation')
            ->for($fileUpload,'defaultFileUpload')
            ->for($user, 'createdBy')
            ->create();
    }
}
