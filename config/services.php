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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'dnsmasq' => [
        'leases' => [
            'path' => env('DNSMASQ_LEASES_PATH'),
        ],
    ],

    'reolink' => [
        'endpoint' => env('REOLINK_ENDPOINT'),
        'username' => env('REOLINK_USERNAME', 'admin'),
        'password' => env('REOLINK_PASSWORD', ''),
    ],

    'glinet' => [
        'endpoint' => env('GLINET_ENDPOINT'),
        'username' => env('GLINET_USERNAME'),
        'password' => env('GLINET_PASSWORD'),
    ],

    'mothership' => [
        'test' => [
            'endpoint' => env('MOTHERSHIP_TEST_ENDPOINT'),
        ],
        'production' => [
            'endpoint' => env('MOTHERSHIP_PRODUCTION_ENDPOINT'),
        ],
        'local' => [
            'endpoint' => env('MOTHERSHIP_LOCAL_ENDPOINT'),
        ],
        'demo' => [
            'endpoint' => env('MOTHERSHIP_DEMO_ENDPOINT'),
        ],
    ],
];
