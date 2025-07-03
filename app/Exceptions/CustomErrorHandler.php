<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomErrorHandler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
    public function render($request, Throwable $exception): Response
    {
        // Handle highlight_file() errors specifically
        if (str_contains($exception->getMessage(), 'highlight_file')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Server configuration error',
                    'message' => 'Please contact system administrator'
                ], 500);
            }

            return response()->view('errors.500', [
                'message' => 'Server configuration error. Please contact administrator.'
            ], 500);
        }

        // Handle Symfony errors in production
        if (app()->environment('production') && str_contains(get_class($exception), 'Symfony')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Internal server error',
                    'message' => 'Something went wrong'
                ], 500);
            }

            return response()->view('errors.500', [
                'message' => 'Something went wrong. Please try again later.'
            ], 500);
        }

        return parent::render($request, $exception);
    }
}
