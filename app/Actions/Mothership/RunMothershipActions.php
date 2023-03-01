<?php

namespace App\Actions\Mothership;

use App\Support\Mothership;
use Illuminate\Support\Facades\Storage;

class RunMothershipActions
{
    public function execute()
    {
        if(!!Storage::exists(Mothership::MOTHERSHIP_TOKEN_FILENAME)) {
            return;
        }

        if(!Mothership::make()->isOnline()) {
            return;
        }

        app(HandleUploadRequests::class)->execute();
        app(SendDiscoveredCamerasToMothership::class)->execute();
        app(SendCamerasWithInvalidCredentialsToMothership::class)->execute();
        app(GetCredentialsForUnauthenticatedCameras::class)->execute();
    }
}
