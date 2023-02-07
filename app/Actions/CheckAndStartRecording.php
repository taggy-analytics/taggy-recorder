<?php

namespace App\Actions;

use App\Enums\CameraRecordingMode;
use App\Enums\CameraStatus;
use App\Models\Camera;

class CheckAndStartRecording
{
    public function execute()
    {
        $cameras = Camera::where('status', CameraStatus::READY)
            ->where('mode', CameraRecordingMode::ALWAYS)
            ->get();

        foreach($cameras as $camera) {
            if(!$camera->isRecording()) {
                $camera->startRecording();
            }
        }
    }
}
