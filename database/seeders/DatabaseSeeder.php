<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            HousingTypeSeeder::class,
            HousingUnitSeeder::class,
            OccupierSeeder::class,
            NoteSeeder::class,
        ]);
    }
}