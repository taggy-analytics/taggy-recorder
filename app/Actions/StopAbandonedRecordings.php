<?php

namespace App\Actions;

use App\Enums\CameraStatus;
use App\Enums\RecordingMode;
use App\Models\Camera;

class StopAbandonedRecordings
{
    public function execute()
    {
        $cameras = Camera::where('status', CameraStatus::OFFLINE)
            ->get();

        foreach($cameras as $camera) {
            if($camera->isRecording()) {
                $camera->stopRecording();
            }
        }
    }
}
