<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class SystemController extends Controller
{
    /**
     * Display system information
     */
    public function info()
    {
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_connection' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
            'mail_driver' => config('mail.default'),
            'timezone' => config('app.timezone'),
            'debug_mode' => config('app.debug'),
            'environment' => config('app.env'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ];

        // Storage information
        $storageInfo = [
            'storage_path' => storage_path(),
            'storage_size' => $this->getDirectorySize(storage_path()),
            'logs_size' => $this->getDirectorySize(storage_path('logs')),
            'cache_size' => $this->getDirectorySize(storage_path('framework/cache')),
        ];

        // Database information
        try {
            $databaseInfo = [
                'connection_status' => 'Connected',
                'database_name' => config('database.connections.' . config('database.default') . '.database'),
                'host' => config('database.connections.' . config('database.default') . '.host'),
                'port' => config('database.connections.' . config('database.default') . '.port'),
            ];
        } catch (\Exception $e) {
            $databaseInfo = [
                'connection_status' => 'Failed: ' . $e->getMessage(),
            ];
        }

        return view('central.system.info', compact('systemInfo', 'storageInfo', 'databaseInfo'));
    }

    /**
     * Display system logs
     */
    public function logs()
    {
        $logFiles = [];
        $logPath = storage_path('logs');
        
        if (File::exists($logPath)) {
            $files = File::files($logPath);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'log') {
                    $logFiles[] = [
                        'name' => $file->getFilename(),
                        'size' => $this->formatBytes($file->getSize()),
                        'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                        'path' => $file->getPathname(),
                    ];
                }
            }
        }

        // Sort by modification time (newest first)
        usort($logFiles, function ($a, $b) {
            return strtotime($b['modified']) - strtotime($a['modified']);
        });

        return view('central.system.logs', compact('logFiles'));
    }

    /**
     * Clear application cache
     */
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            return redirect()->back()->with('success', 'All caches cleared successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    /**
     * Enable maintenance mode
     */
    public function enableMaintenance(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:255',
            'retry' => 'nullable|integer|min:60',
        ]);

        try {
            $options = [];
            if ($request->message) {
                $options['--message'] = $request->message;
            }
            if ($request->retry) {
                $options['--retry'] = $request->retry;
            }

            Artisan::call('down', $options);
            
            return redirect()->back()->with('success', 'Maintenance mode enabled.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to enable maintenance mode: ' . $e->getMessage());
        }
    }

    /**
     * Disable maintenance mode
     */
    public function disableMaintenance()
    {
        try {
            Artisan::call('up');
            
            return redirect()->back()->with('success', 'Maintenance mode disabled.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to disable maintenance mode: ' . $e->getMessage());
        }
    }

    /**
     * Get directory size in bytes
     */
    private function getDirectorySize($directory)
    {
        if (!File::exists($directory)) {
            return 0;
        }

        $size = 0;
        $files = File::allFiles($directory);
        
        foreach ($files as $file) {
            $size += $file->getSize();
        }

        return $this->formatBytes($size);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
