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
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
        });
        
        // Handle database connection errors gracefully
        $exceptions->render(function (\PDOException $e, $request) {
            // For health check endpoints, return success even if DB is down
            if ($request->is('health') || $request->is('api/health') || $request->is('/')) {
                return response()->json([
                    'status' => 'ok',
                    'service' => 'Mazen Maher Chat API',
                    'database' => 'unavailable',
                    'message' => 'Database connection not available',
                ], 200);
            }
            
            // For other requests, return proper error
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Database connection error',
                    'error' => $e->getMessage(),
                ], 503);
            }
        });
        
        // Handle general database exceptions
        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            // For health check endpoints, return success even if DB is down
            if ($request->is('health') || $request->is('api/health') || $request->is('/')) {
                return response()->json([
                    'status' => 'ok',
                    'service' => 'Mazen Maher Chat API',
                    'database' => 'unavailable',
                    'message' => 'Database connection not available',
                ], 200);
            }
            
            // For other requests, return proper error
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Database query error',
                    'error' => $e->getMessage(),
                ], 503);
            }
        });
    })->create();
