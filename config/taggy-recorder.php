<?php

return [
    'video-conversion' => [
        'segment-duration' => 1,
        'thumbnails' => [
            'nth' => 20,
        ]
    ],
    'releases-to-keep' => 4,
    'mothership-logging-key' => env('MOTHERSHIP_LOGGING_KEY'),
    'enable-api-docs-key' => env('ENABLE_API_DOCS_KEY'),
    'recording' => [
        'restart-aborted-recordings-timeout' => 15,
    ],
    'log-requests' => env('LOG_REQUESTS', false),
];
