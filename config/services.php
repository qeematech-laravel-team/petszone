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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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
    'fawry' => [
        'merchant_code' => env('FAWRY_MERCHANT_CODE'),
        'hash_key' => env('FAWRY_HASH_KEY'),
        'base_url' => rtrim((string) env('FAWRY_API_BASE_URL', 'https://atfawry.fawrystaging.com'), '/'),
        // Switch between legacy charge API and the newer init API.
        // - charge: POST {base_url}/ECommerceWeb/Fawry/payments/charge
        // - init:   POST {init_base_url}/fawrypay-api/api/payments/init
        'api_mode' => env('FAWRY_API_MODE', 'init'), // charge|init
        'init_base_url' => rtrim((string) env('FAWRY_INIT_BASE_URL', env('FAWRY_API_BASE_URL', 'https://atfawry.fawrystaging.com')), '/'),
        // Hosted checkout docs use "express" signature (returnUrl + sorted items).
        // Some merchant profiles require the "standard" signature (paymentMethod + amount).
        'signature_mode' => env('FAWRY_SIGNATURE_MODE', 'express'), // express|standard
    ],
    'sms_misr' => [
        'username' => env('SMS_MISR_USERNAME'),
        'password' => env('SMS_MISR_PASSWORD'),
        'sender' => env('SMS_MISR_SENDER'),
        'environment' => env('SMS_MISR_ENVIRONMENT', 2),
    ],
    'geidea' => [
        'public_key' => env('GEIDEA_PUBLIC_KEY'),
        'api_password' => env('GEIDEA_API_PASSWORD'),
        'base_url' => env('GEIDEA_BASE_URL'),
        'callback_url' => env('GEIDEA_CALLBACK_URL'),
    ],
];
