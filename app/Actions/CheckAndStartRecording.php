<?php

namespace App\Actions;

use App\Enums\CameraStatus;
use App\Models\Camera;

class CheckAndStartRecording
{
    public function execute()
    {
        foreach(Camera::where('status', CameraStatus::READY)->get() as $camera) {
            if(!$camera->isRecording()) {
                $camera->startRecording();
            }
        }
    }
}
