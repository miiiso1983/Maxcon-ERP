<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supported Languages
    |--------------------------------------------------------------------------
    |
    | This array contains all the languages supported by the application.
    | Each language has its configuration including direction, flag, etc.
    |
    */
    'supported' => [
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'direction' => 'ltr',
            'flag' => '🇺🇸',
            'enabled' => true,
        ],
        'ar' => [
            'name' => 'Arabic',
            'native' => 'العربية',
            'direction' => 'rtl',
            'flag' => '🇮🇶',
            'enabled' => true,
        ],
        'ku' => [
            'name' => 'Kurdish',
            'native' => 'کوردی',
            'direction' => 'rtl',
            'flag' => '🏴',
            'enabled' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    |
    | This is the default language that will be used when no language
    | is specified or when the requested language is not available.
    |
    */
    'default' => env('APP_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Fallback Language
    |--------------------------------------------------------------------------
    |
    | This is the fallback language that will be used when a translation
    | is not available in the current language.
    |
    */
    'fallback' => env('APP_FALLBACK_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Language Detection
    |--------------------------------------------------------------------------
    |
    | Configure how the application should detect the user's preferred language.
    |
    */
    'detection' => [
        'browser' => true,  // Detect from browser Accept-Language header
        'session' => true,  // Store in session
        'url' => true,      // Allow URL parameter override
    ],

    /*
    |--------------------------------------------------------------------------
    | RTL Languages
    |--------------------------------------------------------------------------
    |
    | List of language codes that use right-to-left text direction.
    |
    */
    'rtl_languages' => ['ar', 'ku', 'fa', 'he', 'ur'],

    /*
    |--------------------------------------------------------------------------
    | Date Formats
    |--------------------------------------------------------------------------
    |
    | Define date formats for each language.
    |
    */
    'date_formats' => [
        'en' => [
            'short' => 'M d, Y',
            'long' => 'F j, Y',
            'datetime' => 'M d, Y H:i',
        ],
        'ar' => [
            'short' => 'd/m/Y',
            'long' => 'j F Y',
            'datetime' => 'd/m/Y H:i',
        ],
        'ku' => [
            'short' => 'Y/m/d',
            'long' => 'j F Y',
            'datetime' => 'Y/m/d H:i',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Number Formats
    |--------------------------------------------------------------------------
    |
    | Define number formats for each language.
    |
    */
    'number_formats' => [
        'en' => [
            'decimal_separator' => '.',
            'thousands_separator' => ',',
            'currency_symbol' => '$',
            'currency_position' => 'before',
        ],
        'ar' => [
            'decimal_separator' => '.',
            'thousands_separator' => ',',
            'currency_symbol' => 'د.ع',
            'currency_position' => 'after',
        ],
        'ku' => [
            'decimal_separator' => '.',
            'thousands_separator' => ',',
            'currency_symbol' => 'د.ع',
            'currency_position' => 'after',
        ],
    ],
];
