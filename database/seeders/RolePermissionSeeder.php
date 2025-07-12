<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin gets all permissions
        $adminRole = Role::where('name', 'admin')->first();
        $allPermissions = Permission::all();
        $adminRole->permissions()->sync($allPermissions->pluck('id'));

        // Manager gets housing, occupier, notes, and reports permissions
        $managerRole = Role::where('name', 'manager')->first();
        $managerPermissions = Permission::whereIn('module', ['housing_units', 'occupiers', 'notes', 'reports'])->get();
        $managerRole->permissions()->sync($managerPermissions->pluck('id'));

        // Viewer gets only view permissions
        $viewerRole = Role::where('name', 'viewer')->first();
        $viewerPermissions = Permission::whereIn('name', [
            'housing_units.view',
            'occupiers.view',
            'notes.view',
            'reports.view'
        ])->get();
        $viewerRole->permissions()->sync($viewerPermissions->pluck('id'));
    }
}