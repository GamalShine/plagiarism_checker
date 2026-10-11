<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'cloudconvert' => [
        'api_key' => env('CLOUDCONVERT_API_KEY'),
        'api_url' => env('CLOUDCONVERT_API_URL', 'https://api.cloudconvert.com/v2'),
        'request_timeout' => (int) env('CLOUDCONVERT_REQUEST_TIMEOUT', 30),
        'max_wait_seconds' => (int) env('CLOUDCONVERT_MAX_WAIT_SECONDS', 240),
        'poll_interval_seconds' => (int) env('CLOUDCONVERT_POLL_INTERVAL_SECONDS', 3),
    ],

];
