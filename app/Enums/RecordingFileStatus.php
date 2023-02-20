<?php

namespace App\Enums;

enum RecordingFileStatus: string
{
    case CREATED = 'created';
    case TO_BE_THUMBNAILED = 'to-be-thumbnailed';
    case THUMBNAIL_CREATED = 'thumbnail-created';
    case TO_BE_UPLOADED = 'to-be-uploaded';
    case UPLOADED = 'uploaded';

    public static function default()
    {
        return self::CREATED;
    }
}
