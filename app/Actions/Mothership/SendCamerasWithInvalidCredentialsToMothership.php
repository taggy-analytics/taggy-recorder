<?php

namespace App\Actions\Mothership;

use App\Enums\CameraStatus;
use App\Models\Camera;
use App\Support\Mothership;

class SendCamerasWithInvalidCredentialsToMothership
{
    public function execute()
    {
        Camera::query()
            ->whereIn('status', [CameraStatus::AUTHENTICATION_FAILED, CameraStatus::OFFLINE])
            ->get()
            ->filter(fn(Camera $camera) => $camera->credentials_status->invalidCredentialsDiscoveredAt && !$camera->credentials_status->invalidCredentialsReportedAt)
            ->each(fn(Camera $camera) => Mothership::make()->reportInvalidCameraCredentials($camera));
    }
}
