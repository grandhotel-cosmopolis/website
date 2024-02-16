<?php

namespace Database\Seeders;

use App\Models\Permissions;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (config('app.env') === 'local') {
            User::factory()
                ->count(10)
                ->create();

            $devUser = User::query()->where('email', 'dev@grandhotel-cosmopolis.org')->first();
            if (is_null($devUser)) {
                /** @var User $devUser */
                $devUser = User::query()->firstOrCreate([
                    'name' => 'Grandhotel User',
                    'email' => 'dev@grandhotel-cosmopolis.org',
                    'email_verified_at' => Carbon::now(),
                    'password' => Hash::make('supersafe'),
                    'remember_token' => Str::random(10),
                ]);
            }
            $devUser->givePermissionTo(Permissions::cases());
        }
    }
}
