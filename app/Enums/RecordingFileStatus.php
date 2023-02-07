<?php

namespace App\Enums;

enum RecordingFileStatus: string
{
    case CREATED = 'created';

    public static function default()
    {
        return self::CREATED;
    }
}
