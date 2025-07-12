<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Administrator with full system access',
                'is_active' => true,
            ],
            [
                'name' => 'manager',
                'description' => 'Manager with unit and occupier management access',
                'is_active' => true,
            ],
            [
                'name' => 'viewer',
                'description' => 'Viewer with read-only access',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}