<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HousingUnitController;
use App\Http\Controllers\OccupierController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // User Management Routes
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class);
    });
    
    // Housing Unit Management Routes
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::resource('housing-units', HousingUnitController::class);
    });
    
    // Occupier Management Routes
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::resource('occupiers', OccupierController::class);
    });
    
    // Notes Routes
    Route::resource('notes', NoteController::class);
    
    // Reports Routes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/occupancy', [ReportController::class, 'occupancy'])->name('reports.occupancy');
    Route::get('/reports/vacancy', [ReportController::class, 'vacancy'])->name('reports.vacancy');
    Route::get('/reports/activity', [ReportController::class, 'activity'])->name('reports.activity');
});

require __DIR__.'/auth.php';