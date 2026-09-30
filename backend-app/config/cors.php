<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    // Flutter Web runs on a temporary localhost port during local development.
    'allowed_origins' => [],
    'allowed_origins_patterns' => ['/\\Ahttps?:\\/\\/(localhost|127\\.0\\.0\\.1)(:\\d+)?\\z/'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
