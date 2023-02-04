<?php

namespace App\Enums;

enum CameraStatus: string
{
    case DISCOVERED = 'discovered';

    case READY = 'ready';
    case AUTHENTICATION_FAILED = 'authentication-failed';
    case NOT_REACHABLE = 'not-reachable';
    case STREAM_NOT_FOUND = 'stream-not-found';
    case CONNECTION_REFUSED = 'connection-refused';
    case UNKNOWN_ERROR = 'unknown-error';
}
