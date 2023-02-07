<?php

namespace App\Enums;

enum RecordingMode: string
{
    case AUTOMATIC = 'automatic';
    case MANUAL = 'manual';
    case SCHEDULE = 'schedule';

    public static function default()
    {
        return self::AUTOMATIC;
    }
}
