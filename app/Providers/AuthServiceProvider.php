<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        //
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Define Gates based on permissions
        Gate::define('manage-users', function ($user) {
            return $user->hasPermission('users.view') || $user->hasPermission('users.create') || 
                   $user->hasPermission('users.edit') || $user->hasPermission('users.delete');
        });

        Gate::define('manage-housing-units', function ($user) {
            return $user->hasPermission('housing_units.view') || $user->hasPermission('housing_units.create') || 
                   $user->hasPermission('housing_units.edit') || $user->hasPermission('housing_units.delete');
        });

        Gate::define('manage-occupiers', function ($user) {
            return $user->hasPermission('occupiers.view') || $user->hasPermission('occupiers.create') || 
                   $user->hasPermission('occupiers.edit') || $user->hasPermission('occupiers.delete');
        });

        Gate::define('manage-notes', function ($user) {
            return $user->hasPermission('notes.view') || $user->hasPermission('notes.create') || 
                   $user->hasPermission('notes.edit') || $user->hasPermission('notes.delete');
        });

        Gate::define('view-reports', function ($user) {
            return $user->hasPermission('reports.view') || $user->hasPermission('reports.export');
        });

        Gate::define('view-dashboard', function ($user) {
            return $user->hasPermission('dashboard.view');
        });

        Gate::define('view-audit-logs', function ($user) {
            return $user->hasPermission('system.audit');
        });

        Gate::define('manage-system', function ($user) {
            return $user->hasPermission('system.settings');
        });
    }
}