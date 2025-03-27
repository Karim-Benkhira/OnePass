<?php

use App\Http\Controllers\DeviceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PasswordController;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login'); 

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/passwords', [PasswordController::class, 'store']);
    Route::get('/passwords', [PasswordController::class, 'index']);
    Route::put('/passwords/{password}', [PasswordController::class, 'update']);
    Route::delete('/passwords/{password}', [PasswordController::class, 'destroy']);
    Route::get('/passwords/{password}', [PasswordController::class, 'show']);
});

use App\Http\Controllers\DeviceController;
use App\Http\Controllers\IpManagementController;


Route::middleware('auth:sanctum')->group(function () {

    Route::post('/devices/check', [DeviceController::class, 'checkDevice']);
    Route::get('/devices', [DeviceController::class, 'listDevices']);
    Route::get('/devices/verify', [DeviceController::class, 'verifyDevice']);

});




    Route::prefix('ip')->group(function () {
        Route::get('/', [IpManagementController::class, 'index']);
        Route::post('/whitelist', [IpManagementController::class, 'addToWhitelist']);
        Route::post('/blacklist', [IpManagementController::class, 'addToBlacklist']);
        Route::delete('/remove', [IpManagementController::class, 'remove']);
        Route::get('/check/{ip}', [IpManagementController::class, 'checkIp']);
    });
});

