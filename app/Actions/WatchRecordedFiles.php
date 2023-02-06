<?php

namespace App\Actions;

use App\Actions\Mothership\GetCredentialsForUnauthenticatedCameras;
use App\Actions\Mothership\SendDiscoveredCamerasToMothership;
use App\CameraTypes\CameraType;
use App\Models\Camera;
use App\Support\Uploader;
use Spatie\Watcher\Watch;

class WatchRecordedFiles
{
    public function execute()
    {
        Watch::path(storage_path("app/cameras"))
            ->onAnyChange(function (string $type, string $path) {
                if ($type === Watch::EVENT_TYPE_FILE_CREATED) {
                    info(1);
                    Uploader::make()->register($path);
                }
            })
            ->start();
    }
}
