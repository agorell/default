<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\HousingUnitController;
use App\Http\Controllers\Api\OccupierController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Protected API routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    
    // Users API (Admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::apiResource('users', UserController::class);
    });
    
    // Housing Units API (Admin and Manager)
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::apiResource('housing-units', HousingUnitController::class);
    });
    
    // Occupiers API (Admin and Manager)
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::apiResource('occupiers', OccupierController::class);
        Route::post('/occupiers/{occupier}/move-out', [OccupierController::class, 'moveOut']);
    });
    
    // Notes API (Admin and Manager)
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::apiResource('notes', NoteController::class);
    });
    
    // Reports API (All authenticated users)
    Route::get('/reports/occupancy', [ReportController::class, 'occupancy']);
    Route::get('/reports/vacancy', [ReportController::class, 'vacancy']);
    Route::get('/reports/activity', [ReportController::class, 'activity']);
});