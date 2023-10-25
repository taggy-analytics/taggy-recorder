<?php

namespace App\Enums;

enum RecordingStatus: string
{
    case CREATED = 'created';
    case PREPARING_PREPROCESSING = 'preparing-preprocessing';
    case CREATED_RECORDING_FILES_IN_DB = 'created-recording-files-in-db';
    case THUMBNAILS_SELECTED = 'thumbnails-selected';
    case THUMBNAILS_CREATED = 'thumbnails-created';
    case ZIP_FILE_CREATED = 'zip-file-created';
    case ZIP_FILE_UPLOADED = 'zip-file-uploaded';
    case CREATING_MOVIE = 'creating-movie';
    case MOVIE_CREATED = 'movie-created';
    case MOVIE_UPLOADED = 'movie-uploaded';
    case TO_BE_DELETED = 'to-be-deleted';
    case READY_FOR_REPORTING_TO_MOTHERSHIP = 'ready-for-reporting-to-mothership';
    case REPORTED_TO_MOTHERSHIP = 'reported-to-mothership';
    case SESSION_NOT_FOUND = 'session-not-found';

    public static function default()
    {
        return self::CREATED;
    }
}
