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

        $this->runAction(SendLogToMothership::class);
        $this->runAction(CheckIfRecorderIsAssignedToOrganization::class);
        $this->runAction(SendReportablesToMothership::class);
        $this->runAction(HandleUploadRequests::class);
        $this->runAction(SendCamerasWithInvalidCredentialsToMothership::class);
        $this->runAction(SendDiscoveredCamerasToMothership::class);
        $this->runAction(ReportRecordingsToMothership::class);
        $this->runAction(GetCredentialsForUnauthenticatedCameras::class);
        $this->runAction(CheckForDeletedRecordings::class);
    }

    private function runAction($action)
    {
        try {
            app($action)->execute();
        }
        catch(\Exception $exception) {
            report($exception);
        }
    }
}
