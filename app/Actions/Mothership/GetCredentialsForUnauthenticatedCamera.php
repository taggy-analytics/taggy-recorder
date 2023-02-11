<?php

namespace App\Actions\Mothership;

use App\Enums\CameraStatus;
use App\Models\Camera;

class GetCredentialsForUnauthenticatedCamera extends MothershipAction
{
    protected function executeAction()
    {
        foreach(Camera::where('status', CameraStatus::AUTHENTICATION_FAILED)->get() as $camera) {
            $credentials = $this->mothership->getCameraCredentials($camera);
            $camera->update(['credentials' => $credentials]);
            if($camera->getStatus() == CameraStatus::READY) {
                $this->mothership->reportDiscoveredCamera($camera);
            }
        }
    }
}
