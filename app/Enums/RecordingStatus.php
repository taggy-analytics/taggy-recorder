<?php

namespace App\Enums;

enum RecordingStatus: string
{
    case CREATED = 'created';
    case PREPARING_PREPROCESSING = 'preparing-preprocessing';
    case THUMBNAILS_SELECTED = 'thumbnails-selected';
    case THUMBNAILS_CREATED = 'thumbnails-created';
    case ZIP_FILE_CREATED = 'zip-file-created';
    case ZIP_FILE_UPLOADED = 'zip-file-uploaded';
    case CREATING_MOVIE = 'creating-movie';
    case MOVIE_CREATED = 'movie-created';
    case MOVIE_UPLOADED = 'movie-uploaded';

    public static function default()
    {
        return self::CREATED;
    }
}
