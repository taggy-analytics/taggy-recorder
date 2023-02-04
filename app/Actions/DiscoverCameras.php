<?php

namespace App\Actions;

use App\Actions\Mothership\GetCredentialsForUnauthenticatedCameras;
use App\Actions\Mothership\SendDiscoveredCamerasToMothership;
use App\CameraTypes\CameraType;
use App\Models\Camera;

class DiscoverCameras
{
    public function execute()
    {
        CameraType::discoverCameras();

        foreach(Camera::all() as $camera) {
            $camera->getStatus();
        }

        app(SendDiscoveredCamerasToMothership::class)->execute();
        app(GetCredentialsForUnauthenticatedCameras::class)->execute();
    }
}
