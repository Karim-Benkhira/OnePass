<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PasswordController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('passwords', PasswordController::class);
});