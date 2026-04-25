<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\UserTypeMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register aliases for middleware
        $middleware->alias([
            'user.type' => UserTypeMiddleware::class,
        ]);
        
        // Optional: Add middleware to specific groups
        $middleware->web([
            // Add to web group
        ]);
        
        $middleware->api([
            // Add to api group
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();