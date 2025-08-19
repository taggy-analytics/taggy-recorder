<?php

return [
    'video-conversion' => [
        'segment-duration' => env('HLS_SEGMENT_DURATION', 6),
        'thumbnails' => [
            'nth' => 20,
        ]
    ],
    'releases-to-keep' => 4,
    'mothership-logging-key' => env('MOTHERSHIP_LOGGING_KEY'),
    'temperature-log-min' => 72,
    'temperature-log-min-size' => 100,
    'enable-api-docs-key' => env('ENABLE_API_DOCS_KEY'),
    'recording' => [
        'restart-aborted-recordings-timeout' => 15,
    ],
    'log-requests' => env('LOG_REQUESTS', false),
    'hostname' => env('HOSTNAME', 'taggy'),
    'ffmpeg' => [
        'logging' => env('FFMPEG_LOGGING', false),
        'logging-level' => env('FFMPEG_LOGGING_LEVEL', 'info'),
        'record-audio' => env('FFMPEG_RECORD_AUDIO', true),
    ],
    'date-time-tolerance' => 2000,    // if the time difference between recorder and device is > x, recorder time will be set to device time
    'software-repository' => 'taggy-analytics/taggy-recorder',
];
