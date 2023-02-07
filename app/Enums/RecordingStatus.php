<?php

namespace App\Enums;

enum RecordingStatus: string
{
    case CREATED = 'created';

    public static function default()
    {
        return self::CREATED;
    }
}
