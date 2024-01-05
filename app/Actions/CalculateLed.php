<?php

namespace App\Actions;

use App\Enums\CameraStatus;
use App\Enums\LedColor;
use App\Models\Camera;
use App\Support\Mothership;
use App\Support\Recorder;

class CalculateLed
{
    public function execute()
    {
        $recorder = Recorder::make();

        if(!$recorder->isUpdatingFirmware()) {
            $recorder->led(LedColor::GREEN, 0.5);
        }
        elseif(!Camera::noCameraIsRecording()) {
            $recorder->led(LedColor::RED, 0.5);
        }
        elseif($recorder->isUploading()) {
            $recorder->led(LedColor::BLUE, 0.5);
        }
        elseif(Mothership::make()->isOnline(1) && $this->cameraIsAvailable()) {
            $recorder->led([LedColor::BLUE, LedColor::RED], 1);
        }
        elseif(Mothership::make()->isOnline(1)) {
            $recorder->led(LedColor::BLUE);
        }
        elseif($this->cameraIsAvailable()) {
            $recorder->led(LedColor::RED);
        }
        else {
            $recorder->led(LedColor::GREEN);
        }
    }

    private function cameraIsAvailable()
    {
        return Camera::where('status', CameraStatus::READY)->count() > 0;
    }
}
