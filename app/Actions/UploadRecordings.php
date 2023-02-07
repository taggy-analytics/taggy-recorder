<?php

namespace App\Actions;

use App\Actions\Mothership\GetCredentialsForUnauthenticatedCameras;
use App\Actions\Mothership\SendDiscoveredCamerasToMothership;
use App\CameraTypes\CameraType;
use App\Enums\RecordingFileType;
use App\Models\Camera;
use App\Models\Recording;
use App\Support\Mothership;
use App\Support\Uploader;
use Illuminate\Support\Str;
use Spatie\Watcher\Watch;

class UploadRecordings
{
    private $interval = 20;
    public function execute()
    {
        while (true) {
            //Mothership::make()->isOnline();
            info('Checking for recordings to upload');
            sleep($this->interval);
        }
    }
}
