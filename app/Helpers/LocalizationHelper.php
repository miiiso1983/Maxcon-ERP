<?php

namespace App\Helpers;

use App\Services\LanguageService;
use Carbon\Carbon;

class LocalizationHelper
{
    /**
     * Format a date according to current locale
     */
    public static function formatDate($date, string $format = 'short'): string
    {
        if (!$date) {
            return '';
        }

        try {
            if (!$date instanceof Carbon) {
                $date = Carbon::parse($date);
            }

            return LanguageService::formatDate($date, $format);
        } catch (\Exception $e) {
            // If parsing fails, return the original value as string
            return (string) $date;
        }
    }

    /**
     * Format a number according to current locale
     */
    public static function formatNumber(float $number, int $decimals = 2): string
    {
        return LanguageService::formatNumber($number, $decimals);
    }

    /**
     * Format currency according to current locale
     */
    public static function formatCurrency(float $amount): string
    {
        return LanguageService::formatCurrency($amount);
    }

    /**
     * Get localized direction class for CSS
     */
    public static function getDirectionClass(): string
    {
        return LanguageService::isRTL() ? 'rtl' : 'ltr';
    }

    /**
     * Get text alignment class based on direction
     */
    public static function getTextAlignClass(): string
    {
        return LanguageService::isRTL() ? 'text-end' : 'text-start';
    }

    /**
     * Get float direction class
     */
    public static function getFloatClass(): string
    {
        return LanguageService::isRTL() ? 'float-end' : 'float-start';
    }

    /**
     * Get margin/padding direction classes
     */
    public static function getMarginStartClass(string $size = '2'): string
    {
        return LanguageService::isRTL() ? "me-{$size}" : "ms-{$size}";
    }

    public static function getMarginEndClass(string $size = '2'): string
    {
        return LanguageService::isRTL() ? "ms-{$size}" : "me-{$size}";
    }

    public static function getPaddingStartClass(string $size = '2'): string
    {
        return LanguageService::isRTL() ? "pe-{$size}" : "ps-{$size}";
    }

    public static function getPaddingEndClass(string $size = '2'): string
    {
        return LanguageService::isRTL() ? "ps-{$size}" : "pe-{$size}";
    }

    /**
     * Get border direction classes
     */
    public static function getBorderStartClass(): string
    {
        return LanguageService::isRTL() ? 'border-end' : 'border-start';
    }

    public static function getBorderEndClass(): string
    {
        return LanguageService::isRTL() ? 'border-start' : 'border-end';
    }

    /**
     * Get localized day names
     */
    public static function getDayNames(): array
    {
        $locale = LanguageService::getCurrentLanguage();
        
        $days = [
            'en' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
            'ar' => ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'],
            'ku' => ['یەکشەممە', 'دووشەممە', 'سێشەممە', 'چوارشەممە', 'پێنجشەممە', 'هەینی', 'شەممە'],
        ];

        return $days[$locale] ?? $days['en'];
    }

    /**
     * Get localized month names
     */
    public static function getMonthNames(): array
    {
        $locale = LanguageService::getCurrentLanguage();
        
        $months = [
            'en' => [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ],
            'ar' => [
                'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
                'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
            ],
            'ku' => [
                'کانوونی دووەم', 'شوبات', 'ئازار', 'نیسان', 'ئایار', 'حوزەیران',
                'تەمووز', 'ئاب', 'ئەیلوول', 'تشرینی یەکەم', 'تشرینی دووەم', 'کانوونی یەکەم'
            ],
        ];

        return $months[$locale] ?? $months['en'];
    }

    /**
     * Convert numbers to localized format
     */
    public static function localizeNumbers(string $text): string
    {
        $locale = LanguageService::getCurrentLanguage();
        
        if ($locale === 'ar') {
            $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            return str_replace($western, $arabic, $text);
        }
        
        return $text;
    }
}
