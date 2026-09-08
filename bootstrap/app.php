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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\EnsureAdminAuthenticated::class,
            'admin.permission' => \App\Http\Middleware\EnsureAdminPermission::class,
            'cors' => \App\Http\Middleware\CorsMiddleware::class,
        ]);

        // ========== EXCLUDE JAZZCASH CALLBACK FROM CSRF ==========
        $middleware->validateCsrfTokens(except: [
            'jazzcash/callback',
            'jazzcash/ipn',
            'jazzcash/return',
            'payment/jazzcash/callback', // Alternative if you use different route
        ]);
        // ========================================================
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();