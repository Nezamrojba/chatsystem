<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    try {
        return response()->json([
            'status' => 'ok',
            'service' => 'Mazen Maher Chat API',
            'version' => '1.0.0',
            'database' => \DB::connection()->getPdo() ? 'connected' : 'unavailable',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'ok',
            'service' => 'Mazen Maher Chat API',
            'version' => '1.0.0',
            'database' => 'unavailable',
            'message' => 'Database connection not configured',
        ]);
    }
});

// Health check endpoint (also available at root)
Route::get('/health', function () {
    try {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'service' => 'Mazen Maher Chat API',
            'database' => \DB::connection()->getPdo() ? 'connected' : 'unavailable',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'service' => 'Mazen Maher Chat API',
            'database' => 'unavailable',
            'message' => 'Database connection not configured',
        ]);
    }
});
