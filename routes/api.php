<?php
use App\Http\Controllers\DeviceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IpManagementController;

Route::get('/', function () {
    return response()->json(['message' => 'Password Manager API']);
});

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/devices/verify', [DeviceController::class, 'verifyDevice'])
    ->name('device.verify')
    ->middleware('signed');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Password routes - using apiResource for cleaner definition
    Route::apiResource('passwords', PasswordController::class);

    // Device routes
    Route::post('/devices/check', [DeviceController::class, 'checkDevice']);
    Route::get('/devices', [DeviceController::class, 'listDevices']);
});

// IP Management routes
Route::prefix('ip')->group(function () {
    Route::get('/', [IpManagementController::class, 'index']);
    Route::post('/whitelist', [IpManagementController::class, 'addToWhitelist']);
    Route::post('/blacklist', [IpManagementController::class, 'addToBlacklist']);
    Route::delete('/remove', [IpManagementController::class, 'remove']);
    Route::get('/check/{ip}', [IpManagementController::class, 'checkIp']);
});