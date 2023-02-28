<?php

namespace App\Actions\Mothership;

use App\Enums\CameraStatus;
use App\Models\Camera;
use App\Support\Mothership;

class SendCamerasWithInvalidCredentialsToMothership
{
    public function execute()
    {
        $cameras = Camera::query()
            ->whereIn('status', [CameraStatus::AUTHENTICATION_FAILED, CameraStatus::OFFLINE])
            ->get()
            ->filter(fn(Camera $camera) => $camera->credentials_status->invalidCredentialsDiscoveredAt && !$camera->credentials_status->newCredentialsReportedAt);

        if($cameras->count() > 0) {
            app(SendCameraWithInvalidCredentialsToMothership::class)
                ->execute();
        }
    }
}
