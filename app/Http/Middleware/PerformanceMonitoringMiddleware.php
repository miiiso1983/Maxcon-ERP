<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Modules\Performance\Services\PerformanceMonitoringService;
use Illuminate\Support\Facades\Log;

class PerformanceMonitoringMiddleware
{
    protected PerformanceMonitoringService $monitor;

    public function __construct(PerformanceMonitoringService $monitor)
    {
        $this->monitor = $monitor;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip monitoring for certain routes
        if ($this->shouldSkipMonitoring($request)) {
            return $next($request);
        }

        // Start monitoring
        $this->monitor->startRequest($request);

        $response = $next($request);

        // End monitoring and log results
        try {
            $metrics = $this->monitor->endRequest();

            // Log slow requests
            if ($metrics['duration'] > 2000) { // 2 seconds
                Log::warning('Slow request detected', [
                    'url' => $metrics['url'],
                    'method' => $metrics['method'],
                    'duration' => $metrics['duration'],
                    'memory_used' => $metrics['memory_used'],
                    'query_count' => $metrics['query_count'],
                ]);
            }

            // Add performance headers for debugging
            if (config('app.debug')) {
                $response->headers->set('X-Response-Time', $metrics['duration'] . 'ms');
                $response->headers->set('X-Memory-Usage', round($metrics['memory_used'] / 1024 / 1024, 2) . 'MB');
                $response->headers->set('X-Query-Count', $metrics['query_count']);
            }

        } catch (\Exception $e) {
            Log::error('Performance monitoring failed', [
                'error' => $e->getMessage(),
                'url' => $request->fullUrl(),
            ]);
        }

        return $response;
    }

    /**
     * Determine if monitoring should be skipped for this request
     */
    private function shouldSkipMonitoring(Request $request): bool
    {
        $skipRoutes = [
            'performance.metrics',
            'performance.alerts',
            'telescope.*',
            'horizon.*',
            '_debugbar.*',
        ];

        $currentRoute = $request->route()?->getName();

        foreach ($skipRoutes as $pattern) {
            if (fnmatch($pattern, $currentRoute)) {
                return true;
            }
        }

        // Skip AJAX requests for metrics
        if ($request->ajax() && str_contains($request->path(), 'metrics')) {
            return true;
        }

        // Skip static assets
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/i', $request->path())) {
            return true;
        }

        return false;
    }
}
