<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
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
                'phone' => '555-0001',
                'role_id' => $adminRole->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Housing Manager',
                'email' => 'manager@housingmanagement.com',
                'password' => Hash::make('manager123'),
                'phone' => '555-0002',
                'role_id' => $managerRole->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'System Viewer',
                'email' => 'viewer@housingmanagement.com',
                'password' => Hash::make('viewer123'),
                'phone' => '555-0003',
                'role_id' => $viewerRole->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'John Smith',
                'email' => 'john.smith@example.com',
                'password' => Hash::make('password123'),
                'phone' => '555-0104',
                'role_id' => $managerRole->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@example.com',
                'password' => Hash::make('password123'),
                'phone' => '555-0105',
                'role_id' => $viewerRole->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}