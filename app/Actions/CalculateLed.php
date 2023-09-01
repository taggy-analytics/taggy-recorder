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

        if(!Camera::noCameraIsRecording()) {
            $recorder->led(LedColor::RED, 500);
        }
        elseif($recorder->isUploading()) {
            $recorder->led(LedColor::BLUE, 500);
        }
        elseif(Mothership::make()->isOnline()) {
            $recorder->led(LedColor::BLUE);
        }
        elseif(Camera::where('status', CameraStatus::READY)->count() > 0) {
            $recorder->led(LedColor::RED);
        }
        else {
            $recorder->led(LedColor::GREEN);
        }
    }
}
