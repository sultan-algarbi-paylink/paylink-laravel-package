<?php

return [
    'merchant' => [
        'testing' => [
            'api_id' => env('PAYLINK_TESTING_APP_ID', null),
            'secret_key' => env('PAYLINK_TESTING_SECRET_KEY', null),
            'persist_token' => env('PAYLINK_TESTING_PERSIST_TOKEN', false),
        ],

        'production' => [
            'api_id' => env('PAYLINK_PRODUCTION_APP_ID', null),
            'secret_key' => env('PAYLINK_PRODUCTION_SECRET_KEY', null),
            'persist_token' => env('PAYLINK_PRODUCTION_PERSIST_TOKEN', false),
        ]
    ],
    'partner' => [
        'testing' => [
            'profile_no' => env('PAYLINK_TESTING_PROFILE_NO', null),
            'api_key' => env('PAYLINK_TESTING_API_KEY', null),
            'persist_token' => env('PAYLINK_TESTING_PERSIST_TOKEN', false),
        ],

        'production' => [
            'profile_no' => env('PAYLINK_PRODUCTION_PROFILE_NO', null),
            'api_key' => env('PAYLINK_PRODUCTION_API_KEY', null),
            'persist_token' => env('PAYLINK_PRODUCTION_PERSIST_TOKEN', false),
        ]
    ]
];
