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

    'key' => [
        'api_secret_key' => env('API_SECRET_KEY'),
    ],


    'appstore' => [
        'key' => env('APPSTORE_KEY_ID'),
        'issuer_id' => env('APPSTORE_ISSUER_ID'),
        'private_key' => env('APPSTORE_PRIVATE_KEY_PATH'),
    ],

    'package' => [
        'name' => env('PACKAGE_NAME'),
    ],

    'google' => [
        'places_key' => env('GOOGLE_PLACE_KEY'),
    ],

    'branch' => [
        'key' => env('BRANCH_KEY'),
        'secret' => env('BRANCH_SECRET'),
    ],

    'fcm' => [
        'server_key' => env('FCM_SERVER_KEY'),
        'project_id' => env('FCM_PROJECT_ID'),
        'base_url'   => env('FCM_BASE_URL'),
    ],

    'android' => [
        'package' => env('ANDROID_PACKAGE'),
    ],

    'ios' => [
        'app_id' => env('IOS_APP_ID'),
    ],

    'subscription' => [
        'package_name' => env('PACKAGE_NAME'),
    ],
    
    'google_play' => [
        'credentials' => env('GOOGLE_APPLICATION_CREDENTIALS'),
        'package_name' => env('GOOGLE_PLAY_PACKAGE'),
    ],
    'apple' => [
        'shared_secret' => env('APPLE_SHARED_SECRET'),
        'in_app_env' => env('APPLE_ENV', 'sandbox'), // sandbox or production
        'key_id' => env('APPLE_KEY_ID'),
        'issuer_id' => env('APPLE_ISSUER_ID'),
        'bundle_id' => env('APPLE_BUNDLE_ID')
    ],

];
