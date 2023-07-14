<?php

namespace App\Actions\Mothership;

use App\Enums\CameraStatus;
use App\Models\Camera;
use App\Support\Mothership;

/*
class GetCredentialsForUnauthenticatedCameras
{
    public function execute()
    {
        foreach(Camera::where('status', CameraStatus::AUTHENTICATION_FAILED)->get() as $camera) {
            $credentials = Mothership::make()->getCameraCredentials($camera);
            if($credentials != $camera->credentials) {
                $camera->update(['credentials' => $credentials]);
                if($camera->getStatus() == CameraStatus::READY) {
                    Mothership::make()->reportDiscoveredCamera($camera);
                }
            }
        }
    }
}
*/
