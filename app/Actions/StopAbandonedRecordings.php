<?php

namespace App\Actions;

use App\Enums\CameraStatus;
use App\Enums\RecordingMode;
use App\Models\Camera;

class StopAbandonedRecordings
{
    public function execute()
    {
        $cameras = Camera::whereNot('status', CameraStatus::READY)
            ->get();

        foreach($cameras as $camera) {
            if($camera->isRecording()) {
                info('Stopping recording for camera #' . $camera->id);
                $camera->stopRecording();
            }
        }
    }
}
