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
            $reverbKey = config('broadcasting.connections.reverb.key', env('REVERB_APP_KEY'));
            
            // If default is 'reverb' but Reverb is not configured, fall back to 'null'
            if ($default === 'reverb' && (empty($reverbKey) || $reverbKey === null)) {
                config(['broadcasting.default' => 'null']);
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
