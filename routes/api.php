<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceController;

// Normal authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Device management routes
    Route::post('/devices/check', [DeviceController::class, 'checkDevice']);
    Route::get('/devices', [DeviceController::class, 'listDevices']);
    Route::get('/devices/verify', [DeviceController::class, 'verifyDevice']);
});