<?php

namespace App\Actions\Mothership;

use App\Models\Camera;
use App\Support\Mothership;
use Illuminate\Support\Facades\Storage;

class RunMothershipActions
{
    public function execute()
    {
        if(!Storage::exists(Mothership::MOTHERSHIP_TOKEN_FILENAME)) {
            return;
        }

        if(!Mothership::make()->isOnline()) {
            return;
        }

        if(!Camera::noCameraIsRecording()) {
            return;
        }

        app(SendLogToMothership::class)->execute();
        app(CheckIfRecorderIsAssignedToOrganization::class)->execute();
        app(HandleUploadRequests::class)->execute();
        app(SendDiscoveredCamerasToMothership::class)->execute();
        app(SendCamerasWithInvalidCredentialsToMothership::class)->execute();
        app(ReportRecordingsToMothership::class)->execute();
        app(GetCredentialsForUnauthenticatedCameras::class)->execute();
        app(CheckForDeletedRecordings::class)->execute();
    }
}
