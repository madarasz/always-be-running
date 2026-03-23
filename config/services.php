<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
    'twitch' => [
        'client_id' => env('TWITCH_CLIENT_ID'),
        'client_secret' => env('TWITCH_CLIENT_SECRET'),
    ],
    'google' => [
        'frontend_api' => env('GOOGLE_FRONTEND_API'),
        'backend_api' => env('GOOGLE_BACKEND_API'),
        'map_id' => env('GOOGLE_MAP_ID'),
    ],
    'netrunnerdb' => [
        'client_id' => env('NETRUNNERDB_CLIENT_ID'),
        'client_secret' => env('NETRUNNERDB_CLIENT_SECRET'),
        'redirect' => env('NETRUNNERDB_REDIRECT_URI'),
        'guzzle' => [
            'timeout' => 10.0,
            'connect_timeout' => 5.0,
        ],
    ],

];
