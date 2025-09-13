<?php

namespace App\Enums;

enum RecordingFileStatus: string
{
    case CREATED = 'created';
    case TO_BE_THUMBNAILED = 'to-be-thumbnailed';
    case THUMBNAIL_CREATED = 'thumbnail-created';
    case TO_BE_UPLOADED = 'to-be-uploaded';
    case ALREADY_IN_LIVESTREAM = 'already-in-livestream';
    case UPLOADED = 'uploaded';
    case VIDEO_NOT_AVAILABLE_ANYMORE = 'video-not-available-anymore';

    public static function default()
    {
        return self::CREATED;
    }
}
