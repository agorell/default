<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $viewerRole = Role::where('name', 'viewer')->first();

        $users = [
            [
                'name' => 'System Administrator',
                'email' => 'admin@housingmanagement.com',
                'password' => Hash::make('admin123'),
                'role_id' => $adminRole->id,
                'is_active' => true,
                'phone' => '555-0001',
                'address' => '123 Admin Street, Management City, MC 12345',
            ],
            [
                'name' => 'Housing Manager',
                'email' => 'manager@housingmanagement.com',
                'password' => Hash::make('manager123'),
                'role_id' => $managerRole->id,
                'is_active' => true,
                'phone' => '555-0002',
                'address' => '456 Manager Ave, Management City, MC 12345',
            ],
            [
                'name' => 'System Viewer',
                'email' => 'viewer@housingmanagement.com',
                'password' => Hash::make('viewer123'),
                'role_id' => $viewerRole->id,
                'is_active' => true,
                'phone' => '555-0003',
                'address' => '789 Viewer Road, Management City, MC 12345',
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(['email' => $user['email']], $user);
        }
    }
}