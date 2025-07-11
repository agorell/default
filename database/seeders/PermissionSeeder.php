<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // User Management
            [
                'name' => 'users.view',
                'display_name' => 'View Users',
                'description' => 'View user listings and details',
                'module' => 'users',
                'is_active' => true,
            ],
            [
                'name' => 'users.create',
                'display_name' => 'Create Users',
                'description' => 'Create new user accounts',
                'module' => 'users',
                'is_active' => true,
            ],
            [
                'name' => 'users.edit',
                'display_name' => 'Edit Users',
                'description' => 'Edit existing user accounts',
                'module' => 'users',
                'is_active' => true,
            ],
            [
                'name' => 'users.delete',
                'display_name' => 'Delete Users',
                'description' => 'Delete user accounts',
                'module' => 'users',
                'is_active' => true,
            ],
            
            // Housing Unit Management
            [
                'name' => 'housing_units.view',
                'display_name' => 'View Housing Units',
                'description' => 'View housing unit listings and details',
                'module' => 'housing_units',
                'is_active' => true,
            ],
            [
                'name' => 'housing_units.create',
                'display_name' => 'Create Housing Units',
                'description' => 'Create new housing units',
                'module' => 'housing_units',
                'is_active' => true,
            ],
            [
                'name' => 'housing_units.edit',
                'display_name' => 'Edit Housing Units',
                'description' => 'Edit existing housing units',
                'module' => 'housing_units',
                'is_active' => true,
            ],
            [
                'name' => 'housing_units.delete',
                'display_name' => 'Delete Housing Units',
                'description' => 'Delete housing units',
                'module' => 'housing_units',
                'is_active' => true,
            ],
            
            // Occupier Management
            [
                'name' => 'occupiers.view',
                'display_name' => 'View Occupiers',
                'description' => 'View occupier listings and details',
                'module' => 'occupiers',
                'is_active' => true,
            ],
            [
                'name' => 'occupiers.create',
                'display_name' => 'Create Occupiers',
                'description' => 'Create new occupier records',
                'module' => 'occupiers',
                'is_active' => true,
            ],
            [
                'name' => 'occupiers.edit',
                'display_name' => 'Edit Occupiers',
                'description' => 'Edit existing occupier records',
                'module' => 'occupiers',
                'is_active' => true,
            ],
            [
                'name' => 'occupiers.delete',
                'display_name' => 'Delete Occupiers',
                'description' => 'Delete occupier records',
                'module' => 'occupiers',
                'is_active' => true,
            ],
            
            // Notes Management
            [
                'name' => 'notes.view',
                'display_name' => 'View Notes',
                'description' => 'View notes and documentation',
                'module' => 'notes',
                'is_active' => true,
            ],
            [
                'name' => 'notes.create',
                'display_name' => 'Create Notes',
                'description' => 'Create new notes',
                'module' => 'notes',
                'is_active' => true,
            ],
            [
                'name' => 'notes.edit',
                'display_name' => 'Edit Notes',
                'description' => 'Edit existing notes',
                'module' => 'notes',
                'is_active' => true,
            ],
            [
                'name' => 'notes.delete',
                'display_name' => 'Delete Notes',
                'description' => 'Delete notes',
                'module' => 'notes',
                'is_active' => true,
            ],
            
            // Reports
            [
                'name' => 'reports.view',
                'display_name' => 'View Reports',
                'description' => 'View system reports and analytics',
                'module' => 'reports',
                'is_active' => true,
            ],
            [
                'name' => 'reports.export',
                'display_name' => 'Export Reports',
                'description' => 'Export reports to various formats',
                'module' => 'reports',
                'is_active' => true,
            ],
            
            // Dashboard
            [
                'name' => 'dashboard.view',
                'display_name' => 'View Dashboard',
                'description' => 'Access the main dashboard',
                'module' => 'dashboard',
                'is_active' => true,
            ],
            
            // System Administration
            [
                'name' => 'system.audit',
                'display_name' => 'View Audit Logs',
                'description' => 'View system audit logs',
                'module' => 'system',
                'is_active' => true,
            ],
            [
                'name' => 'system.settings',
                'display_name' => 'System Settings',
                'description' => 'Manage system settings',
                'module' => 'system',
                'is_active' => true,
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}