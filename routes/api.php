<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\HousingUnitController;
use App\Http\Controllers\Api\OccupierController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\ReportController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    
    // User Management API
    Route::apiResource('users', UserController::class);
    
    // Housing Unit Management API
    Route::apiResource('housing-units', HousingUnitController::class);
    
    // Occupier Management API
    Route::apiResource('occupiers', OccupierController::class);
    
    // Notes API
    Route::apiResource('notes', NoteController::class);
    
    // Reports API
    Route::get('/reports/occupancy', [ReportController::class, 'occupancy']);
    Route::get('/reports/vacancy', [ReportController::class, 'vacancy']);
    Route::get('/reports/activity', [ReportController::class, 'activity']);
    Route::get('/reports/dashboard', [ReportController::class, 'dashboard']);
});

// Rate limiting for API
Route::middleware('throttle:60,1')->group(function () {
    // Add any rate-limited routes here
});