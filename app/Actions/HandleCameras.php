<?php

namespace App\Actions;

use App\CameraTypes\CameraType;
use App\Models\Camera;

class HandleCameras
{
    public function execute()
    {
        CameraType::discoverCameras();

        foreach(Camera::all() as $camera) {
            $camera->getStatus();
        }

        app(StopAbandonedRecordings::class)->execute();
        app(CheckAndStartRecording::class)->execute();
    }
}
