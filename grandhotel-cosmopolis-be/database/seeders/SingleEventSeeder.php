<?php

namespace Database\Seeders;

use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\SingleEvent;
use App\Models\User;
use Illuminate\Database\Seeder;

class SingleEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (config('app.env') === 'local') {
            $user = User::factory()->create();
            SingleEvent::factory()
                ->count(5)
                ->for(EventLocation::factory()->create())
                ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
                ->for($user, 'createdBy')
                ->create();
        }
    }
}
