<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'Mazen Maher Chat API',
        'version' => '1.0.0'
    ]);
});

// Health check endpoint (also available at root)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'service' => 'Mazen Maher Chat API'
    ]);
});
