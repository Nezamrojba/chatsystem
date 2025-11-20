<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:9000',
        'http://127.0.0.1:9000',
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://localhost', // For Capacitor Android apps
        'http://127.0.0.1', // For Capacitor Android apps
        'capacitor://localhost', // For Capacitor apps
        'https://straight-grazia-nezam-74b0170a.koyeb.app',
        env('FRONTEND_URL', 'http://localhost:9000'),
    ],

    'allowed_origins_patterns' => [
        '#^http://localhost(:\d+)?$#', // Allow localhost with or without port
        '#^http://127\.0\.0\.1(:\d+)?$#', // Allow 127.0.0.1 with or without port
        '#^capacitor://localhost$#', // Allow Capacitor protocol
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];

