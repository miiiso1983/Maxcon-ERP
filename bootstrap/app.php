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
    ->withMiddleware(function (Middleware $middleware): void {
        // Only add middleware that exists
        $middleware->alias([
            'language' => \App\Http\Middleware\LanguageMiddleware::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\LanguageMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Simple error handling for production
        $exceptions->render(function (Throwable $e, $request) {
            // Handle highlight_file errors
            if (str_contains($e->getMessage(), 'highlight_file') ||
                str_contains($e->getMessage(), 'Call to undefined function')) {

                return response('<h1>Service Temporarily Unavailable</h1><p>The system is being updated. Please try again in a few minutes.</p>', 500);
            }

            // Handle Sanctum errors
            if (str_contains($e->getMessage(), 'SanctumServiceProvider')) {
                return response('<h1>Configuration Error</h1><p>System is being configured. Please try again later.</p>', 500);
            }

            // Handle view errors
            if (str_contains($e->getMessage(), 'Target class [view] does not exist')) {
                return response('<h1>System Error</h1><p>Application is being initialized. Please refresh the page.</p>', 500);
            }

            // Generic production error
            if (app()->environment('production')) {
                return response('<h1>Service Unavailable</h1><p>We are experiencing technical difficulties. Please try again later.</p>', 500);
            }
        });
    })->create();
