<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'xendit' => [
        'api_key' => env('XENDIT_API_KEY'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'from_number' => env('TWILIO_FROM_NUMBER'),
    ],

    // RajaOngkir shipping rates
    'rajaongkir' => [
        'key' => env('RAJAONGKIR_KEY'),
        'base_url' => env('RAJAONGKIR_BASE_URL', 'https://api.rajaongkir.com/starter'), // starter|basic|pro
    ],

    'jnt' => [
        'username' => env('JNT_USERNAME'),
        'password' => env('JNT_PASSWORD'),
        // Example: https://developer.jet.co.id or full base incl. /sandbox if needed
        'base_url' => env('JNT_BASE_URL', 'https://developer.jet.co.id'),
        // Allow overriding endpoint paths if needed
        'paths' => [
            'create_order' => env('JNT_PATH_CREATE', '/jts-idn-ecommerce-api/api/order/create'),
            'cancel_order' => env('JNT_PATH_CANCEL', '/jts-idn-ecommerce-api/api/order/cancel'),
            'tariff' => env('JNT_PATH_TARIFF', '/jandt_track/inquiry.action'),
            'track' => env('JNT_PATH_TRACK', '/jandt-order-ifd-web/track/trackAction!tracking.action'),
        ],
    ],
];
