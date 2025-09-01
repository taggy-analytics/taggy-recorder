<?php

namespace App\Actions;

use App\Enums\CameraStatus;
use App\Enums\LedColor;
use App\Models\Camera;
use App\Support\Mothership;
use App\Support\Recorder;
use Illuminate\Support\Facades\Cache;

class CalculateLed
{
    public function execute()
    {
        Cache::lock('calculateLeds', 10)->get(function () {
            Recorder::make()->inProMode() ? $this->proModeLedCalculation() : $this->communityModeLedCalculation();
        });
    }

    private function cameraIsAvailable()
    {
        return Camera::where('status', CameraStatus::READY)->count() > 0;
    }

    private function proModeLedCalculation()
    {
        $recorder = Recorder::make();

        if ($recorder->isUpdatingFirmware()) {
            $recorder->led(LedColor::GREEN, 0.5);
        } elseif (! Camera::noCameraIsRecording()) {
            $recorder->led(LedColor::RED, $recorder->isLivestreaming() ? 0.25 : 0.5);
        } elseif ($recorder->isUploading(calculateLed: false)) {
            $recorder->led(LedColor::BLUE, 0.5);
        } elseif (Mothership::make()->isOnline(1) && $this->cameraIsAvailable()) {
            $recorder->led([LedColor::BLUE, LedColor::RED], 1);
        } elseif (Mothership::make()->isOnline(1)) {
            $recorder->led(LedColor::BLUE);
        } elseif ($this->cameraIsAvailable()) {
            $recorder->led(LedColor::RED);
        } else {
            $recorder->led(LedColor::GREEN);
        }
    }

    private function communityModeLedCalculation()
    {
        $recorder = Recorder::make();

        if ($recorder->isUpdatingFirmware()) {
            $recorder->led(LedColor::GREEN, 0.5);
        } elseif (! Camera::noCameraIsRecording()) {
            $recorder->led(LedColor::RED, $recorder->isLivestreaming() ? 0.25 : 0.5);
        }
        // ToDo: fix when we upload to cloud
        /*
        elseif($recorder->isUploading(calculateLed: false)) {
            $recorder->led(LedColor::BLUE, 0.5);
        }
        */
        elseif (Recorder::make()->connectedToInternet() && $this->cameraIsAvailable()) {
            $recorder->led([LedColor::BLUE, LedColor::RED], 1);
        } elseif (Recorder::make()->connectedToInternet()) {
            $recorder->led(LedColor::BLUE);
        } elseif ($this->cameraIsAvailable()) {
            $recorder->led(LedColor::RED);
        } else {
            $recorder->led(LedColor::GREEN);
        }
    }
}
