<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\IpManagementController;

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/devices/check', [DeviceController::class, 'checkDevice']);
    Route::get('/devices', [DeviceController::class, 'listDevices']);
    Route::get('/devices/verify', [DeviceController::class, 'verifyDevice']);


    Route::prefix('ip')->group(function () {
        Route::get('/', [IpManagementController::class, 'index']);
        Route::post('/whitelist', [IpManagementController::class, 'addToWhitelist']);
        Route::post('/blacklist', [IpManagementController::class, 'addToBlacklist']);
        Route::delete('/remove', [IpManagementController::class, 'remove']);
        Route::get('/check/{ip}', [IpManagementController::class, 'checkIp']);
    });
});