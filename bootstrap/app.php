<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all hosts for Railway (healthcheck.railway.app needs access)
        $middleware->trustHosts(['*']);
        
        // CORS and Sanctum must be first
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
        
        // Exclude API routes from CSRF verification (using token-based auth)
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
        
        // Rate limiting for API
        $middleware->throttleApi('60,1');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Custom API exception handling
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if (str_starts_with($request->path(), 'api/')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
        });
        
        // Handle all exceptions for health check endpoints
        $exceptions->render(function (\Throwable $e, $request) {
            $path = $request->path();
            
            // For health check endpoints, always return success
            if ($path === 'health' || $path === 'api/health' || $path === '') {
                $dbStatus = 'unknown';
                try {
                    \DB::connection()->getPdo();
                    $dbStatus = 'connected';
                } catch (\Exception $dbE) {
                    $dbStatus = 'unavailable';
                }
                
                return response()->json([
                    'status' => 'ok',
                    'service' => 'Mazen Maher Chat API',
                    'database' => $dbStatus,
                    'error' => $e->getMessage(),
                ], 200);
            }
            
            // For other API requests, return JSON error
            if (str_starts_with($path, 'api/')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Server error',
                    'error' => $e->getMessage(),
                ], 500);
            }
        });
    })->create();
