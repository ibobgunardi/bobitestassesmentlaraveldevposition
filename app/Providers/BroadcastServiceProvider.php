<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Log when broadcast routes are being registered
        \Log::info('Registering broadcast routes');
        
        // Register broadcast routes with auth middleware
        Broadcast::routes(['middleware' => ['auth:sanctum', 'web']]);
        
        // Log when channels file is being required
        \Log::info('Loading broadcast channels');
        require base_path('routes/channels.php');
        \Log::info('Broadcast service provider booted');
    }
}
