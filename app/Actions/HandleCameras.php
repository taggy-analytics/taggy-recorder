<?php

namespace App\Actions;

use App\Models\Camera;

class HandleCameras
{
    public function execute()
    {
        foreach(Camera::all() as $camera) {
            $camera->getStatus();
        }

        app(StopAbandonedRecordings::class)->execute();
        // app(CheckAndStartRecording::class)->execute();
    }
}
