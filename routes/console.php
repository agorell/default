<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('housing:setup', function () {
    $this->info('Setting up Housing Management System...');
    
    // Run migrations
    $this->call('migrate');
    
    // Seed the database
    $this->call('db:seed');
    
    $this->info('Housing Management System setup complete!');
})->purpose('Setup the Housing Management System');

Artisan::command('housing:stats', function () {
    $totalUnits = \App\Models\HousingUnit::count();
    $occupiedUnits = \App\Models\HousingUnit::where('is_occupied', true)->count();
    $vacantUnits = $totalUnits - $occupiedUnits;
    
    $this->info("Housing Management System Statistics:");
    $this->line("Total Units: {$totalUnits}");
    $this->line("Occupied Units: {$occupiedUnits}");
    $this->line("Vacant Units: {$vacantUnits}");
})->purpose('Display housing statistics');