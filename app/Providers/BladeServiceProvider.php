<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Services\LanguageService;
use App\Helpers\LocalizationHelper;

class BladeServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Direction directive
        Blade::directive('dir', function () {
            return "<?php echo LanguageService::getCurrentDirection(); ?>";
        });

        // RTL check directive
        Blade::directive('isRTL', function () {
            return "<?php echo LanguageService::isRTL() ? 'true' : 'false'; ?>";
        });

        // Format date directive
        Blade::directive('formatDate', function ($expression) {
            return "<?php echo LocalizationHelper::formatDate($expression); ?>";
        });

        // Format number directive
        Blade::directive('formatNumber', function ($expression) {
            return "<?php echo LocalizationHelper::formatNumber($expression); ?>";
        });

        // Format currency directive
        Blade::directive('formatCurrency', function ($expression) {
            return "<?php echo LocalizationHelper::formatCurrency($expression); ?>";
        });

        // Text alignment directive
        Blade::directive('textAlign', function () {
            return "<?php echo LocalizationHelper::getTextAlignClass(); ?>";
        });

        // Float direction directive
        Blade::directive('float', function () {
            return "<?php echo LocalizationHelper::getFloatClass(); ?>";
        });

        // Margin start directive
        Blade::directive('marginStart', function ($expression = '2') {
            return "<?php echo LocalizationHelper::getMarginStartClass($expression); ?>";
        });

        // Margin end directive
        Blade::directive('marginEnd', function ($expression = '2') {
            return "<?php echo LocalizationHelper::getMarginEndClass($expression); ?>";
        });

        // Localize numbers directive
        Blade::directive('localizeNumbers', function ($expression) {
            return "<?php echo LocalizationHelper::localizeNumbers($expression); ?>";
        });

        // Language-specific content
        Blade::if('lang', function ($language) {
            return LanguageService::getCurrentLanguage() === $language;
        });

        // RTL content
        Blade::if('rtl', function () {
            return LanguageService::isRTL();
        });

        // LTR content
        Blade::if('ltr', function () {
            return !LanguageService::isRTL();
        });
    }
}
