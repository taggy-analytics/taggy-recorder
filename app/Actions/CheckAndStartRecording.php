<?php

namespace App\Actions;

use App\Enums\CameraStatus;
use App\Enums\RecordingMode;
use App\Models\Camera;

/*
class CheckAndStartRecording
{
    public function execute()
    {
        $cameras = Camera::where('status', CameraStatus::READY)
            ->where('recording_mode', RecordingMode::AUTOMATIC)
            ->get();

        foreach($cameras as $camera) {
            if(!$camera->isRecording()) {
                $camera->startRecording();
            }
        }
    }
}
*/
