<?php

namespace Database\Seeders;

use App\Models\FileUpload;
use App\Models\User;
use Illuminate\Database\Seeder;

class FileUploadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FileUpload::factory()
            ->count(1)
            ->for(User::factory()->create(), 'uploadedBy')
            ->create();
    }
}
