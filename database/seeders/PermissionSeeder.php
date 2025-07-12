<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User Management
            ['name' => 'users.view', 'description' => 'View users', 'module' => 'users'],
            ['name' => 'users.create', 'description' => 'Create users', 'module' => 'users'],
            ['name' => 'users.edit', 'description' => 'Edit users', 'module' => 'users'],
            ['name' => 'users.delete', 'description' => 'Delete users', 'module' => 'users'],
            
            // Housing Unit Management
            ['name' => 'housing_units.view', 'description' => 'View housing units', 'module' => 'housing_units'],
            ['name' => 'housing_units.create', 'description' => 'Create housing units', 'module' => 'housing_units'],
            ['name' => 'housing_units.edit', 'description' => 'Edit housing units', 'module' => 'housing_units'],
            ['name' => 'housing_units.delete', 'description' => 'Delete housing units', 'module' => 'housing_units'],
            
            // Occupier Management
            ['name' => 'occupiers.view', 'description' => 'View occupiers', 'module' => 'occupiers'],
            ['name' => 'occupiers.create', 'description' => 'Create occupiers', 'module' => 'occupiers'],
            ['name' => 'occupiers.edit', 'description' => 'Edit occupiers', 'module' => 'occupiers'],
            ['name' => 'occupiers.delete', 'description' => 'Delete occupiers', 'module' => 'occupiers'],
            
            // Notes Management
            ['name' => 'notes.view', 'description' => 'View notes', 'module' => 'notes'],
            ['name' => 'notes.create', 'description' => 'Create notes', 'module' => 'notes'],
            ['name' => 'notes.edit', 'description' => 'Edit notes', 'module' => 'notes'],
            ['name' => 'notes.delete', 'description' => 'Delete notes', 'module' => 'notes'],
            
            // Reports
            ['name' => 'reports.view', 'description' => 'View reports', 'module' => 'reports'],
            ['name' => 'reports.export', 'description' => 'Export reports', 'module' => 'reports'],
            
            // System
            ['name' => 'audit_logs.view', 'description' => 'View audit logs', 'module' => 'system'],
            ['name' => 'settings.manage', 'description' => 'Manage system settings', 'module' => 'system'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }
    }
}