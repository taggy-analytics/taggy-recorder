<?php

namespace App\Enums;

enum RecordingStatus: string
{
    case CREATED = 'created';
    case PREPROCESSING = 'preprocessing';
    case PREPROCESSED = 'preprocessed';

    public static function default()
    {
        return self::CREATED;
    }
}
