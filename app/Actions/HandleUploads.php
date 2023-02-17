<?php

namespace App\Actions;

use App\Actions\Mothership\GetCredentialsForUnauthenticatedCameras;
use App\Actions\Mothership\SendDiscoveredCamerasToMothership;
use App\CameraTypes\CameraType;
use App\Enums\RecordingFileType;
use App\Enums\RecordingStatus;
use App\Models\Camera;
use App\Models\Recording;
use App\Support\Mothership;
use App\Support\Uploader;
use Illuminate\Support\Str;
use Spatie\Watcher\Watch;

class HandleUploads
{
    public function execute()
    {
        $mothership = Mothership::make();

        info('Manage uploads');

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
        elseif(true) {

        }
    }
}
