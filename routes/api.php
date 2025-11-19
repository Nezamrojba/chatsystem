<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BatchController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\MessageController;
use Illuminate\Support\Facades\Route;

// Public health check endpoint
Route::get('health', function () {
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

// Public auth routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);
    
    // Conversations routes
    Route::apiResource('conversations', ConversationController::class);
    Route::get('conversations/{conversation}/messages', [ConversationController::class, 'messages']);
    
    // Messages routes
    Route::apiResource('messages', MessageController::class);
    Route::post('messages/{message}/voice', [MessageController::class, 'uploadVoice']);
    
    // Batch operations (reduces API calls)
    Route::post('batch/fetch', [BatchController::class, 'fetch']);
});

