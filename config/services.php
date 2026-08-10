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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/auth/google/callback'),
    ],

    /*
    | SMS gateway used for OTP sign-in. Driver "log" writes the code to
    | storage/logs/laravel.log instead of sending, so the flow is usable in
    | development before a real gateway is wired up.
    */
    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'),
        'endpoint' => env('SMS_ENDPOINT'),
        'api_key' => env('SMS_API_KEY'),
        'sender_id' => env('SMS_SENDER_ID'),
    ],

    /*
    | Steadfast Courier (portal.packzy.com).
    |
    | driver: "log" records the exact payload to storage/logs/laravel.log and
    | fabricates no consignment — safe before credentials exist. Switch to "api"
    | only when the keys below are real, because "api" creates REAL shipments
    | and real cash-on-delivery collections.
    |
    | auto_push is a separate switch on purpose: you can configure and test the
    | credentials with the manual "Send to courier" button before letting orders
    | leave automatically.
    */
    'steadfast' => [
        'driver' => env('STEADFAST_DRIVER', 'log'),
        'base_url' => env('STEADFAST_BASE_URL', 'https://portal.packzy.com/api/v1'),
        'api_key' => env('STEADFAST_API_KEY'),
        'secret_key' => env('STEADFAST_SECRET_KEY'),
        'timeout' => (int) env('STEADFAST_TIMEOUT', 20),

        // Order status that hands a parcel to the courier. Reached only after an
        // admin approves (and, for bank transfer, after payment is verified), so
        // a cancelled or unpaid order never becomes a consignment.
        'push_on_status' => env('STEADFAST_PUSH_ON_STATUS', 'processing'),
        'auto_push' => (bool) env('STEADFAST_AUTO_PUSH', false),

        // 0 = home delivery, 1 = Point/Hub pickup.
        'delivery_type' => (int) env('STEADFAST_DELIVERY_TYPE', 0),
    ],

];
