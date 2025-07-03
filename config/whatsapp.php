<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Business API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WhatsApp Business API integration
    |
    */

    'api_url' => env('WHATSAPP_API_URL', 'https://graph.facebook.com/v18.0'),
    
    'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
    
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    
    'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
    
    'webhook_verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
    
    'webhook_secret' => env('WHATSAPP_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */

    'default_language' => 'en',
    
    'max_retries' => 3,
    
    'rate_limit' => [
        'messages_per_second' => 20,
        'messages_per_minute' => 1000,
        'messages_per_day' => 100000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Templates
    |--------------------------------------------------------------------------
    */

    'templates' => [
        'invoice' => [
            'name' => 'invoice_notification',
            'category' => 'transactional',
        ],
        'payment_reminder' => [
            'name' => 'payment_reminder',
            'category' => 'transactional',
        ],
        'welcome' => [
            'name' => 'welcome_message',
            'category' => 'utility',
        ],
        'order_confirmation' => [
            'name' => 'order_confirmation',
            'category' => 'transactional',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Settings
    |--------------------------------------------------------------------------
    */

    'media' => [
        'max_file_size' => 16 * 1024 * 1024, // 16MB
        'allowed_types' => [
            'image' => ['jpg', 'jpeg', 'png', 'gif'],
            'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'],
            'video' => ['mp4', '3gp'],
            'audio' => ['aac', 'amr', 'mp3', 'ogg'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Settings
    |--------------------------------------------------------------------------
    */

    'queue' => [
        'connection' => env('WHATSAPP_QUEUE_CONNECTION', 'database'),
        'queue' => env('WHATSAPP_QUEUE_NAME', 'whatsapp'),
        'batch_size' => 50,
        'retry_delay' => 300, // 5 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */

    'logging' => [
        'enabled' => env('WHATSAPP_LOGGING_ENABLED', true),
        'channel' => env('WHATSAPP_LOG_CHANNEL', 'single'),
        'level' => env('WHATSAPP_LOG_LEVEL', 'info'),
    ],
];
