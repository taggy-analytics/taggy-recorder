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
    'hostname' => env('HOSTNAME'),
];
