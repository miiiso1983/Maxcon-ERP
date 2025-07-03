<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['web'])
                ->group(base_path('routes/tenant.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'super_admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'master_admin' => \App\Http\Middleware\EnsureMasterAdmin::class,
            'prevent_master_tenant_access' => \App\Http\Middleware\PreventMasterAdminTenantAccess::class,
            'language' => \App\Http\Middleware\LanguageMiddleware::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\LanguageMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle highlight_file() errors for shared hosting
        $exceptions->render(function (Throwable $e, $request) {
            if (str_contains($e->getMessage(), 'highlight_file') ||
                str_contains($e->getMessage(), 'Call to undefined function')) {

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Server configuration error',
                        'message' => 'Please contact system administrator'
                    ], 500);
                }

                return response()->view('errors.500', [
                    'message' => 'Server configuration issue detected. Please contact your hosting provider to enable required PHP functions.',
                    'exception' => config('app.debug') ? $e : null
                ], 500);
            }
        });
    })->create();
