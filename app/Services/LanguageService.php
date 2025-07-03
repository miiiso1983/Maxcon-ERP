<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;

class LanguageService
{

    public static function getSupportedLanguages(): array
    {
        return Config::get('languages.supported', []);
    }

    public static function setLanguage(string $locale): void
    {
        $supportedLanguages = self::getSupportedLanguages();
        if (array_key_exists($locale, $supportedLanguages) && $supportedLanguages[$locale]['enabled']) {
            App::setLocale($locale);
            Session::put('locale', $locale);
        }
    }

    public static function getCurrentLanguage(): string
    {
        return Session::get('locale', Config::get('languages.default', 'en'));
    }

    public static function getCurrentDirection(): string
    {
        $locale = self::getCurrentLanguage();
        $supportedLanguages = self::getSupportedLanguages();
        return $supportedLanguages[$locale]['direction'] ?? 'ltr';
    }

    public static function isRTL(): bool
    {
        return self::getCurrentDirection() === 'rtl';
    }

    public static function getLanguageInfo(string $locale): array
    {
        $supportedLanguages = self::getSupportedLanguages();
        return $supportedLanguages[$locale] ?? $supportedLanguages[Config::get('languages.default', 'en')];
    }

    public static function getCurrentLanguageInfo(): array
    {
        return self::getLanguageInfo(self::getCurrentLanguage());
    }

    public static function formatDate($date, string $format = 'short', ?string $locale = null): string
    {
        if (!$date) {
            return '';
        }

        try {
            // Ensure $date is a Carbon instance
            if (!$date instanceof \Carbon\Carbon) {
                $date = \Carbon\Carbon::parse($date);
            }

            $locale = $locale ?? self::getCurrentLanguage();
            $formats = Config::get("languages.date_formats.{$locale}", Config::get('languages.date_formats.en'));
            $dateFormat = $formats[$format] ?? $formats['short'];

            return $date->format($dateFormat);
        } catch (\Exception $e) {
            // If parsing fails, return the original value as string
            return (string) $date;
        }
    }

    public static function formatNumber(float $number, int $decimals = 2, ?string $locale = null): string
    {
        $locale = $locale ?? self::getCurrentLanguage();
        $format = Config::get("languages.number_formats.{$locale}", Config::get('languages.number_formats.en'));

        return number_format(
            $number,
            $decimals,
            $format['decimal_separator'],
            $format['thousands_separator']
        );
    }

    public static function formatCurrency(float $amount, ?string $locale = null): string
    {
        $locale = $locale ?? self::getCurrentLanguage();
        $format = Config::get("languages.number_formats.{$locale}", Config::get('languages.number_formats.en'));

        $formattedNumber = self::formatNumber($amount, 2, $locale);

        if ($format['currency_position'] === 'before') {
            return $format['currency_symbol'] . ' ' . $formattedNumber;
        }

        return $formattedNumber . ' ' . $format['currency_symbol'];
    }

    public static function detectBrowserLanguage(): ?string
    {
        if (!Config::get('languages.detection.browser', false)) {
            return null;
        }

        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        $supportedLanguages = array_keys(self::getSupportedLanguages());

        foreach (explode(',', $acceptLanguage) as $lang) {
            $lang = trim(explode(';', $lang)[0]);
            $lang = substr($lang, 0, 2); // Get language code only

            if (in_array($lang, $supportedLanguages)) {
                return $lang;
            }
        }

        return null;
    }
}
