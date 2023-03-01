<?php

namespace App\Actions\Mothership;

use App\Enums\RecordingFileStatus;
use App\Enums\RecordingStatus;
use App\Models\Recording;
use App\Models\RecordingFile;
use App\Support\Mothership;

class HandleUploads
{
    public function execute()
    {
        $mothership = Mothership::make();

        $files = RecordingFile::where('status', RecordingFileStatus::TO_BE_UPLOADED)->get();

        if($recording = Recording::where('status', RecordingStatus::ZIP_FILE_CREATED)->first()) {
            if($mothership->isOnline()) {
                $mothership->sendRecordingThumbnails($recording);
                $recording->setStatus(RecordingStatus::ZIP_FILE_UPLOADED);
            }
        }
        elseif($recording = Recording::where('status', RecordingStatus::MOVIE_CREATED)->first()) {
            if($mothership->isOnline()) {
                $mothership->sendRecordingThumbnailsMovie($recording);
                $recording->setStatus(RecordingStatus::MOVIE_UPLOADED);
            }
        }
        elseif($files->count() > 0) {
            if($mothership->isOnline()) {
                foreach($files as $file) {
                    $mothership->sendRecordingFile($file);
                    $file->setStatus(RecordingFileStatus::UPLOADED);
                }
            }
        }
    }
}
