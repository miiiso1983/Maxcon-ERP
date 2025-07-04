<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Handle highlight_file errors
        if (str_contains($exception->getMessage(), 'highlight_file') || 
            str_contains($exception->getMessage(), 'Call to undefined function')) {
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Server configuration error',
                    'message' => 'Please contact system administrator'
                ], 500);
            }

            return response('<h1>Service Temporarily Unavailable</h1><p>The system is being updated. Please try again in a few minutes.</p>', 500);
        }
        
        // Handle Sanctum errors
        if (str_contains($exception->getMessage(), 'SanctumServiceProvider')) {
            return response('<h1>Configuration Error</h1><p>System is being configured. Please try again later.</p>', 500);
        }
        
        // Handle view errors
        if (str_contains($exception->getMessage(), 'Target class [view] does not exist')) {
            return response('<h1>System Error</h1><p>Application is being initialized. Please refresh the page.</p>', 500);
        }
        
        // Handle BadMethodCallException for Laravel version mismatch
        if (str_contains($exception->getMessage(), 'configure does not exist') ||
            str_contains($exception->getMessage(), 'BadMethodCallException')) {
            return response('<h1>System Update Required</h1><p>The application is being updated. Please try again in a few minutes.</p>', 500);
        }
        
        // Generic production error
        if (app()->environment('production')) {
            return response('<h1>Service Unavailable</h1><p>We are experiencing technical difficulties. Please try again later.</p>', 500);
        }

        return parent::render($request, $exception);
    }
}
