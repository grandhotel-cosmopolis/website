<?php

namespace Database\Seeders;
use App\Models\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Permissions::cases() as $permission) {
            Permission::create(['name' => $permission->value]);
        }
    }
}
