<?php

namespace App\Actions\Mothership;

use App\Models\Camera;
use App\Support\Mothership;
use Illuminate\Support\Facades\Storage;

class RunMothershipActions
{
    public function execute()
    {
        /*
        if(!Storage::exists(Mothership::MOTHERSHIP_TOKEN_FILENAME)) {
            return;
        }
        */

        if(!Mothership::make()->isOnline()) {
            return;
        }

        if(!Camera::noCameraIsRecording()) {
            return;
        }

        // $this->runAction(SyncTransactionsWithMothership::class);

        // 2024-09-03 Disable for now; might be responsible for recorder hangups?!
        // 2024-09-06 Enable again because hangups where caused by memory leak in UploadLivestreamSegments
        $this->runAction(ManageWebsocketsConnection::class);

        $this->runAction(SendLogToMothership::class);
        $this->runAction(SendTemperatureLogToMothership::class);
        $this->runAction(SendReportablesToMothership::class);
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
