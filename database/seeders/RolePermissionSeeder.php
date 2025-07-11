<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $viewerRole = Role::where('name', 'viewer')->first();
        
        // Admin gets all permissions
        $allPermissions = Permission::all();
        $adminRole->permissions()->attach($allPermissions);
        
        // Manager permissions
        $managerPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'housing_units.view',
            'housing_units.create',
            'housing_units.edit',
            'housing_units.delete',
            'occupiers.view',
            'occupiers.create',
            'occupiers.edit',
            'occupiers.delete',
            'notes.view',
            'notes.create',
            'notes.edit',
            'notes.delete',
            'reports.view',
            'reports.export',
        ])->get();
        $managerRole->permissions()->attach($managerPermissions);
        
        // Viewer permissions
        $viewerPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'housing_units.view',
            'occupiers.view',
            'notes.view',
            'reports.view',
        ])->get();
        $viewerRole->permissions()->attach($viewerPermissions);
    }
}