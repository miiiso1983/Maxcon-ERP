<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    /**
     * Display system settings
     */
    public function index()
    {
        $settings = [
            'app' => [
                'name' => config('app.name'),
                'url' => config('app.url'),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale'),
                'debug' => config('app.debug'),
                'environment' => config('app.env'),
            ],
            'database' => [
                'connection' => config('database.default'),
                'host' => config('database.connections.' . config('database.default') . '.host'),
                'port' => config('database.connections.' . config('database.default') . '.port'),
                'database' => config('database.connections.' . config('database.default') . '.database'),
            ],
            'cache' => [
                'driver' => config('cache.default'),
                'prefix' => config('cache.prefix'),
            ],
            'session' => [
                'driver' => config('session.driver'),
                'lifetime' => config('session.lifetime'),
                'encrypt' => config('session.encrypt'),
            ],
            'mail' => [
                'driver' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ],
            'queue' => [
                'driver' => config('queue.default'),
                'connection' => config('queue.connections.' . config('queue.default') . '.connection'),
            ],
            'tenancy' => [
                'central_domains' => config('tenancy.central_domains', []),
                'tenant_model' => config('tenancy.tenant_model'),
                'domain_model' => config('tenancy.domain_model'),
            ],
            'system' => [
                'max_tenants' => $this->getSystemSetting('max_tenants', 100),
                'default_license_type' => $this->getSystemSetting('default_license_type', 'basic'),
                'auto_approve_tenants' => $this->getSystemSetting('auto_approve_tenants', false),
                'maintenance_mode' => app()->isDownForMaintenance(),
                'backup_enabled' => $this->getSystemSetting('backup_enabled', true),
                'backup_frequency' => $this->getSystemSetting('backup_frequency', 'daily'),
            ],
            'security' => [
                'force_https' => $this->getSystemSetting('force_https', false),
                'session_timeout' => $this->getSystemSetting('session_timeout', 120),
                'max_login_attempts' => $this->getSystemSetting('max_login_attempts', 5),
                'password_min_length' => $this->getSystemSetting('password_min_length', 8),
                'require_email_verification' => $this->getSystemSetting('require_email_verification', true),
            ],
            'notifications' => [
                'license_expiry_warning_days' => $this->getSystemSetting('license_expiry_warning_days', 30),
                'send_billing_reminders' => $this->getSystemSetting('send_billing_reminders', true),
                'admin_email_notifications' => $this->getSystemSetting('admin_email_notifications', true),
                'system_health_alerts' => $this->getSystemSetting('system_health_alerts', true),
            ]
        ];

        return view('central.settings.index', compact('settings'));
    }

    /**
     * Update system settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.system.max_tenants' => 'required|integer|min:1',
            'settings.system.default_license_type' => 'required|in:basic,standard,premium,enterprise',
            'settings.system.auto_approve_tenants' => 'boolean',
            'settings.system.backup_enabled' => 'boolean',
            'settings.system.backup_frequency' => 'required|in:hourly,daily,weekly',
            'settings.security.force_https' => 'boolean',
            'settings.security.session_timeout' => 'required|integer|min:30|max:1440',
            'settings.security.max_login_attempts' => 'required|integer|min:3|max:10',
            'settings.security.password_min_length' => 'required|integer|min:6|max:20',
            'settings.security.require_email_verification' => 'boolean',
            'settings.notifications.license_expiry_warning_days' => 'required|integer|min:1|max:90',
            'settings.notifications.send_billing_reminders' => 'boolean',
            'settings.notifications.admin_email_notifications' => 'boolean',
            'settings.notifications.system_health_alerts' => 'boolean',
        ]);

        try {
            $settings = $request->input('settings');

            // Update system settings
            foreach ($settings['system'] as $key => $value) {
                $this->updateSystemSetting("system.{$key}", $value);
            }

            // Update security settings
            foreach ($settings['security'] as $key => $value) {
                $this->updateSystemSetting("security.{$key}", $value);
            }

            // Update notification settings
            foreach ($settings['notifications'] as $key => $value) {
                $this->updateSystemSetting("notifications.{$key}", $value);
            }

            // Clear config cache to apply changes
            Artisan::call('config:clear');

            return redirect()->route('central.settings.index')
                ->with('success', 'Settings updated successfully.');

        } catch (\Exception $e) {
            return redirect()->route('central.settings.index')
                ->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Get a system setting value
     */
    private function getSystemSetting(string $key, $default = null)
    {
        // In a real implementation, this would read from a settings table or config file
        // For now, we'll return default values
        $settings = [
            'max_tenants' => 100,
            'default_license_type' => 'basic',
            'auto_approve_tenants' => false,
            'backup_enabled' => true,
            'backup_frequency' => 'daily',
            'force_https' => false,
            'session_timeout' => 120,
            'max_login_attempts' => 5,
            'password_min_length' => 8,
            'require_email_verification' => true,
            'license_expiry_warning_days' => 30,
            'send_billing_reminders' => true,
            'admin_email_notifications' => true,
            'system_health_alerts' => true,
        ];

        return $settings[$key] ?? $default;
    }

    /**
     * Update a system setting value
     */
    private function updateSystemSetting(string $key, $value): void
    {
        // In a real implementation, this would save to a settings table or config file
        // For now, we'll just log the change
        \Log::info("System setting updated: {$key} = " . json_encode($value));
    }
}
