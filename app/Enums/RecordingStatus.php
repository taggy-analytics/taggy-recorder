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
    case SESSION_NOT_FOUND_ON_MOTHERSHIP = 'session-not-found-on-mothership';
    case RECORDER_NOT_FOUND_ON_MOTHERSHIP = 'recorder-not-found-on-mothership';
    case UNKNOWN_MOTHERSHIP_ERROR = 'unknown-mothership-error';
    case DELETING_FILES = 'deleting-files';

    public static function default()
    {
        return self::CREATED;
    }
}
