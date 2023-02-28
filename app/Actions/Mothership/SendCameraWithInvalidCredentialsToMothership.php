<?php

namespace App\Actions\Mothership;

use App\Enums\CameraStatus;
use App\Models\Camera;
use App\Support\Mothership;

class SendCameraWithInvalidCredentialsToMothership extends MothershipAction
{
    public function executeAction()
    {
        $cameras = Camera::query()
            ->whereIn('status', [CameraStatus::AUTHENTICATION_FAILED, CameraStatus::OFFLINE])
            ->get()
            ->filter(fn(Camera $camera) => $camera->credentials_status->invalidCredentialsDiscoveredAt && !$camera->credentials_status->newCredentialsReportedAt);

        foreach($cameras as $camera) {
            Mothership::make()
                ->reportInvalidCameraCredentials($camera);
        }
    }
}
