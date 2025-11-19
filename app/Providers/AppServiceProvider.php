<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Broadcasting\BroadcastManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Ensure broadcaster default is 'null' if Reverb is not configured
        $this->app->resolving(BroadcastManager::class, function ($manager) {
            $default = config('broadcasting.default');
            $reverbKey = env('REVERB_APP_KEY');
            
            // If default is 'reverb' but Reverb is not configured, fall back to 'null'
            if ($default === 'reverb' && (empty($reverbKey) || $reverbKey === null)) {
                config(['broadcasting.default' => 'null']);
            }
        });
        
        // Catch any broadcaster initialization errors and fall back to null
        try {
            $this->app->afterResolving(BroadcastManager::class, function ($manager) {
                try {
                    // Try to get the default connection - if it fails, switch to null
                    $default = config('broadcasting.default');
                    if ($default === 'reverb') {
                        $connection = $manager->connection();
                        // If connection is null or invalid, switch to null broadcaster
                        if (!$connection) {
                            config(['broadcasting.default' => 'null']);
                        }
                    }
                } catch (\Exception $e) {
                    // If there's any error, switch to null broadcaster
                    config(['broadcasting.default' => 'null']);
                }
            });
        } catch (\Exception $e) {
            // Silently fail - we'll handle it in boot()
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Final safeguard: ensure default broadcaster is valid
        try {
            $default = config('broadcasting.default');
            if ($default === 'reverb') {
                $reverbKey = env('REVERB_APP_KEY');
                // If Reverb key is not set, switch to null
                if (empty($reverbKey)) {
                    config(['broadcasting.default' => 'null']);
                }
            }
        } catch (\Exception $e) {
            // If anything fails, ensure we use null broadcaster
            config(['broadcasting.default' => 'null']);
        }
    }
}
