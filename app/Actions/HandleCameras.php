<?php

namespace App\Actions;

use App\Actions\Mothership\GetCredentialsForUnauthenticatedCameras;
use App\Actions\Mothership\SendDiscoveredCamerasToMothership;
use App\CameraTypes\CameraType;
use App\Models\Camera;

class HandleCameras
{
    public function execute()
    {
        info('Handle cameras');

        CameraType::discoverCameras();

        foreach(Camera::all() as $camera) {
            $camera->getStatus();
        }

        app(StopAbandonedRecordings::class)->execute();
        app(CheckAndStartRecording::class)->execute();
        app(SendDiscoveredCamerasToMothership::class)->execute();
        app(GetCredentialsForUnauthenticatedCameras::class)->execute();
    }
}
