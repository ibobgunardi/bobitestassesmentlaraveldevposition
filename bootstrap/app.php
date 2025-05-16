<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\EnforceSeoStandards::class,
        ]);
        
        // Ensure session is started for API routes
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'sanctum/csrf-cookie',
            'login',
            'logout',
            'broadcasting/auth',
            '*', // Temporary for testing
        ]);
        
        // Add CORS headers using built-in middleware
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);
        
        // Ensure stateful API requests work
        // Stateful domains are configured in config/sanctum.php
        $middleware->statefulApi();
        
        // Enable broadcasting routes
        $middleware->alias([
            'broadcasting.auth' => \Illuminate\Broadcasting\BroadcastController::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withProviders([
        \Laravel\Sanctum\SanctumServiceProvider::class,
        \Illuminate\Broadcasting\BroadcastServiceProvider::class,
    ])
    ->create();
