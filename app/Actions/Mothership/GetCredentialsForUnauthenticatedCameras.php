<?php

namespace App\Actions\Mothership;

use App\Enums\CameraStatus;
use App\Models\Camera;

class GetCredentialsForUnauthenticatedCameras
{
    public function execute()
    {
        foreach(Camera::where('status', CameraStatus::AUTHENTICATION_FAILED)->get() as $camera) {
            app(GetCredentialsForUnauthenticatedCamera::class)->execute($camera);
        }
    }
}
