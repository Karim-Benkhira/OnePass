<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:3,1');

// Device verification route (needs to be public for email links)
Route::get('/verify-device', [AuthController::class, 'verifyDevice'])
    ->name('device.verify')
    ->middleware('signed');

Route::get('/test', function () {
    return response()->json(['message' => 'Hello World!']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Password management
    Route::post('/passwords', [PasswordController::class, 'store']);
    Route::get('/passwords', [PasswordController::class, 'index']);
    Route::put('/passwords/{password}', [PasswordController::class, 'update']);
    Route::delete('/passwords/{password}', [PasswordController::class, 'destroy']);
    
    // Device management
    Route::post('/devices/check', [DeviceController::class, 'checkDevice']);
    Route::get('/devices', [DeviceController::class, 'listDevices']);
    Route::post('/verify-device', [DeviceController::class, 'checkDevice']);
});

