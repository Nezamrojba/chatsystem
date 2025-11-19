<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $dbStatus = 'unknown';
    try {
        \DB::connection()->getPdo();
        $dbStatus = 'connected';
    } catch (\Exception $e) {
        $dbStatus = 'unavailable';
    }
    
    return response()->json([
        'status' => 'ok',
        'service' => 'Mazen Maher Chat API',
        'version' => '1.0.0',
        'database' => $dbStatus,
    ]);
});

// Health check endpoint (also available at root)
Route::get('/health', function () {
    $dbStatus = 'unknown';
    try {
        \DB::connection()->getPdo();
        $dbStatus = 'connected';
    } catch (\Exception $e) {
        $dbStatus = 'unavailable';
    }
    
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'service' => 'Mazen Maher Chat API',
        'database' => $dbStatus,
    ]);
});
