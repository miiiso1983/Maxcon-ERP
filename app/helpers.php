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
    /**
     * Get margin end class based on direction
     */
    function marginEnd($size = '3'): string
    {
        return isRtl() ? "ms-{$size}" : "me-{$size}";
    }
}

if (!function_exists('marginStart')) {
    /**
     * Get margin start class based on direction
     */
    function marginStart($size = '3'): string
    {
        return isRtl() ? "me-{$size}" : "ms-{$size}";
    }
}

if (!function_exists('paddingEnd')) {
    /**
     * Get padding end class based on direction
     */
    function paddingEnd($size = '3'): string
    {
        return isRtl() ? "ps-{$size}" : "pe-{$size}";
    }
}

if (!function_exists('paddingStart')) {
    /**
     * Get padding start class based on direction
     */
    function paddingStart($size = '3'): string
    {
        return isRtl() ? "pe-{$size}" : "ps-{$size}";
    }
}

if (!function_exists('textAlign')) {
    /**
     * Get text alignment class based on direction
     */
    function textAlign(): string
    {
        return isRtl() ? 'text-end' : 'text-start';
    }
}

if (!function_exists('floatDirection')) {
    /**
     * Get float class based on direction
     */
    function floatDirection(): string
    {
        return isRtl() ? 'float-end' : 'float-start';
    }
}

if (!function_exists('borderEnd')) {
    /**
     * Get border end class based on direction
     */
    function borderEnd(): string
    {
        return isRtl() ? 'border-start' : 'border-end';
    }
}

if (!function_exists('borderStart')) {
    /**
     * Get border start class based on direction
     */
    function borderStart(): string
    {
        return isRtl() ? 'border-end' : 'border-start';
    }
}

if (!function_exists('formatCurrency')) {
    /**
     * Format currency based on locale
     */
    function formatCurrency($amount): string
    {
        $locale = app()->getLocale();
        
        switch ($locale) {
            case 'ar':
            case 'ku':
                return number_format($amount, 0, '.', ',') . ' د.ع';
            default:
                return 'IQD ' . number_format($amount, 2, '.', ',');
        }
    }
}

if (!function_exists('formatNumber')) {
    /**
     * Format number based on locale
     */
    function formatNumber($number, $decimals = 0): string
    {
        return number_format($number, $decimals, '.', ',');
    }
}

if (!function_exists('formatDate')) {
    /**
     * Format date based on locale
     */
    function formatDate($date, $format = 'short'): string
    {
        if (!$date) {
            return '';
        }

        try {
            // Ensure $date is a Carbon instance
            if (!$date instanceof \Carbon\Carbon) {
                $date = \Carbon\Carbon::parse($date);
            }

            $locale = app()->getLocale();

            switch ($locale) {
                case 'ar':
                case 'ku':
                    return $date->format('d/m/Y');
                default:
                    return $date->format('m/d/Y');
            }
        } catch (\Exception $e) {
            // If parsing fails, return the original value as string
            return (string) $date;
        }
    }
}

if (!function_exists('directionClass')) {
    /**
     * Get CSS class for direction-aware positioning
     */
    function directionClass($ltrClass, $rtlClass): string
    {
        return isRtl() ? $rtlClass : $ltrClass;
    }
}

if (!function_exists('getDateFormat')) {
    /**
     * Get date format based on locale
     */
    function getDateFormat(): string
    {
        $locale = app()->getLocale();
        
        switch ($locale) {
            case 'ar':
            case 'ku':
                return 'd/m/Y';
            default:
                return 'm/d/Y';
        }
    }
}

if (!function_exists('getTimeFormat')) {
    /**
     * Get time format based on locale
     */
    function getTimeFormat(): string
    {
        $locale = app()->getLocale();
        
        switch ($locale) {
            case 'ar':
            case 'ku':
                return 'H:i';
            default:
                return 'h:i A';
        }
    }
}

if (!function_exists('convertNumbers')) {
    /**
     * Convert numbers to Arabic-Indic numerals if needed
     */
    function convertNumbers($text): string
    {
        $locale = app()->getLocale();
        
        if ($locale === 'ar') {
            $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            return str_replace($western, $arabic, $text);
        }
        
        return $text;
    }
}

if (!function_exists('iconDirection')) {
    /**
     * Get appropriate icon direction
     */
    function iconDirection($icon): string
    {
        if (isRtl()) {
            // Flip certain icons for RTL
            $flipMap = [
                'fa-arrow-right' => 'fa-arrow-left',
                'fa-arrow-left' => 'fa-arrow-right',
                'fa-chevron-right' => 'fa-chevron-left',
                'fa-chevron-left' => 'fa-chevron-right',
                'fa-angle-right' => 'fa-angle-left',
                'fa-angle-left' => 'fa-angle-right',
            ];
            
            return $flipMap[$icon] ?? $icon;
        }
        
        return $icon;
    }
}
