<?php

namespace App\Enums;

enum RecordingFileType: string
{
    case PLAYLIST = 'playlist';
    case VIDEO_TS = 'video-ts';
    case VIDEO_M4S = 'video-m4s';

}
