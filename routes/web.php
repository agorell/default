<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HousingUnitController;
use App\Http\Controllers\OccupierController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', function () {
    return redirect('/login');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // User management (Admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class);
    });
    
    // Housing units (Admin and Manager)
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::resource('housing-units', HousingUnitController::class);
    });
    
    // Occupiers (Admin and Manager)
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::resource('occupiers', OccupierController::class);
        Route::post('/occupiers/{occupier}/move-out', [OccupierController::class, 'moveOut'])->name('occupiers.move-out');
    });
    
    // Notes (Admin and Manager)
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::resource('notes', NoteController::class);
    });
    
    // Reports (All authenticated users)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/occupancy', [ReportController::class, 'occupancy'])->name('reports.occupancy');
    Route::get('/reports/vacancy', [ReportController::class, 'vacancy'])->name('reports.vacancy');
    Route::get('/reports/activity', [ReportController::class, 'activity'])->name('reports.activity');
});