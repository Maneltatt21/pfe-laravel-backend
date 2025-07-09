<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\ExchangeController;
use App\Http\Controllers\Api\UserController;

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

// Public routes
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth routes
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);

        // Vehicle routes
        Route::apiResource('vehicles', VehicleController::class);
        Route::post('/vehicles/{vehicle}/archive', [VehicleController::class, 'archive']);
        Route::post('/vehicles/{vehicle}/restore', [VehicleController::class, 'restore']);

        // Vehicle documents routes
        Route::prefix('vehicles/{vehicle}')->group(function () {
            Route::get('/documents/expiring', [DocumentController::class, 'expiring']);
            Route::apiResource('documents', DocumentController::class);
        });

        // Maintenance routes
        Route::prefix('vehicles/{vehicle}')->group(function () {
            Route::apiResource('maintenances', MaintenanceController::class);
            Route::get('/maintenances/upcoming', [MaintenanceController::class, 'upcoming']);
        });

        // Vehicle exchange routes
        Route::apiResource('exchanges', ExchangeController::class);
        Route::post('/exchanges/{exchange}/approve', [ExchangeController::class, 'approve']);
        Route::post('/exchanges/{exchange}/reject', [ExchangeController::class, 'reject']);

        // User management routes (admin only)
        Route::middleware('role:admin')->group(function () {
            Route::apiResource('users', UserController::class);
            Route::post('/users/{user}/assign-vehicle', [UserController::class, 'assignVehicle']);
        });

        // Driver specific routes
        Route::middleware('role:chauffeur')->group(function () {
            Route::get('/my-vehicle', [VehicleController::class, 'myVehicle']);
            Route::get('/my-exchanges', [ExchangeController::class, 'myExchanges']);
        });
    });
});
