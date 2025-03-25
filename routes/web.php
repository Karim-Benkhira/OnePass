<?php

use Illuminate\Support\Facades\Route;

// Return JSON for the root route since this is an API-only application
Route::get('/', function () {
    return response()->json([
        'name' => 'OnePass API',
        'version' => '1.0',
        'status' => 'running'
    ]);
});

Route::fallback(function () {
    return response()->json([
        'message' => 'Not Found. If you\'re looking for the API, use /api/* routes.',
    ], 404);
});
