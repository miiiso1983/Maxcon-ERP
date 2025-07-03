<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\LanguageService;
use Illuminate\Support\Facades\App;

class LanguageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if language is set in URL parameter
        if ($request->has('lang')) {
            LanguageService::setLanguage($request->get('lang'));
        }

        // Set the application locale
        App::setLocale(LanguageService::getCurrentLanguage());

        return $next($request);
    }
}
