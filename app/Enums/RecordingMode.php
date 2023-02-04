<?php

namespace App\Enums;

enum RecordingMode: string
{
    case AUTOMATIC = 'automatic';
    case MANUAL = 'manual';

    public static function default()
    {
        return self::AUTOMATIC;
    }
}
