<?php

if (!function_exists('direction')) {
    /**
     * Get the current language direction (ltr or rtl)
     */
    function direction(): string
    {
        $locale = app()->getLocale();
        $rtlLanguages = ['ar', 'he', 'fa', 'ur', 'ku'];
        return in_array($locale, $rtlLanguages) ? 'rtl' : 'ltr';
    }
}

if (!function_exists('isRtl')) {
    /**
     * Check if current language is RTL
     */
    function isRtl(): bool
    {
        return direction() === 'rtl';
    }
}

if (!function_exists('isLtr')) {
    /**
     * Check if current language is LTR
     */
    function isLtr(): bool
    {
        return direction() === 'ltr';
    }
}

if (!function_exists('marginEnd')) {
    function marginEnd($size = '3') {
        return isRtl() ? "ms-{$size}" : "me-{$size}";
    }
}

if (!function_exists('marginStart')) {
    function marginStart($size = '3') {
        return isRtl() ? "me-{$size}" : "ms-{$size}";
    }
}

if (!function_exists('textAlign')) {
    function textAlign() {
        return isRtl() ? 'text-end' : 'text-start';
    }
}

if (!function_exists('isRtl')) {
    function isRtl() {
        return app()->getLocale() === 'ar';
    }
}

if (!function_exists('direction')) {
    function direction() {
        return isRtl() ? 'rtl' : 'ltr';
    }
}

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount, $currency = 'IQD') {
        $locale = app()->getLocale();
        $formattedAmount = number_format($amount, 2, '.', ',');
        
        if ($locale === 'ar') {
            return "{$formattedAmount} {$currency}";
        }
        
        return "{$currency} {$formattedAmount}";
    }
}

if (!function_exists('borderEnd')) {
    function borderEnd($class = '') {
        $isRtl = app()->getLocale() === 'ar';
        return $isRtl ? "border-start{$class}" : "border-end{$class}";
    }
}

if (!function_exists('borderStart')) {
    function borderStart($class = '') {
        $isRtl = app()->getLocale() === 'ar';
        return $isRtl ? "border-end{$class}" : "border-start{$class}";
    }
}

if (!function_exists('paddingEnd')) {
    function paddingEnd($size) {
        $isRtl = app()->getLocale() === 'ar';
        return $isRtl ? "ps-{$size}" : "pe-{$size}";
    }
}

if (!function_exists('paddingStart')) {
    function paddingStart($size) {
        $isRtl = app()->getLocale() === 'ar';
        return $isRtl ? "pe-{$size}" : "ps-{$size}";
    }
}

if (!function_exists('floatDirection')) {
    function floatDirection() {
        $isRtl = app()->getLocale() === 'ar';
        return $isRtl ? 'float-end' : 'float-start';
    }
}

if (!function_exists('languageClasses')) {
    function languageClasses() {
        $locale = app()->getLocale();
        $classes = [];

        if ($locale === 'ar') {
            $classes[] = 'rtl';
            $classes[] = 'arabic';
        } elseif ($locale === 'ku') {
            $classes[] = 'kurdish';
        } else {
            $classes[] = 'ltr';
            $classes[] = 'english';
        }

        return implode(' ', $classes);
    }
}

if (!function_exists('formatNumber')) {
    function formatNumber($number, $decimals = 0) {
        $locale = app()->getLocale();
        
        if ($locale === 'ar') {
            return number_format($number, $decimals, '.', '،');
        }
        
        return number_format($number, $decimals, '.', ',');
    }
}

if (!function_exists('dateFormat')) {
    function dateFormat() {
        $locale = app()->getLocale();
        
        return match($locale) {
            'ar' => 'd/m/Y',
            'ku' => 'd/m/Y',
            default => 'm/d/Y'
        };
    }
}

if (!function_exists('datetimeFormat')) {
    function datetimeFormat() {
        $locale = app()->getLocale();
        
        return match($locale) {
            'ar' => 'd/m/Y H:i',
            'ku' => 'd/m/Y H:i',
            default => 'm/d/Y H:i'
        };
    }
}
