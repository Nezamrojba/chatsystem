<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BatchController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\MessageController;
use Illuminate\Support\Facades\Route;

// Public health check endpoint
Route::get('health', function () {
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

// Public auth routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);
    
    // Device/FCM token routes
    Route::post('device/register-token', [\App\Http\Controllers\Api\DeviceController::class, 'registerToken']);
    Route::post('device/remove-token', [\App\Http\Controllers\Api\DeviceController::class, 'removeToken']);
    
    // Conversations routes
    Route::apiResource('conversations', ConversationController::class);
    Route::get('conversations/{conversation}/messages', [ConversationController::class, 'messages']);
    
    // Messages routes
    Route::apiResource('messages', MessageController::class);
    Route::post('messages/{message}/voice', [MessageController::class, 'uploadVoice']);
    
    // Batch operations (reduces API calls)
    Route::post('batch/fetch', [BatchController::class, 'fetch']);
});

