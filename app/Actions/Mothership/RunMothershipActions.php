<?php

namespace App\Actions\Mothership;

use App\Models\Camera;
use App\Support\Mothership;
use App\Support\Recorder;
use Illuminate\Http\Client\ConnectionException;

class RunMothershipActions
{
    public function execute()
    {
        requireProMode('Recorder must be in pro mode to connect to mothership.');

        if (! Mothership::make()->isOnline()) {
            return;
        }

        if (Recorder::make()->isLivestreaming()) {
            return;
        }

        if (! Camera::noCameraIsRecording()) {
            return;
        }

        // $this->runAction(SyncTransactionsWithMothership::class);

        // 2024-09-03 Disable for now; might be responsible for recorder hangups?!
        // 2024-09-06 Enable again because hangups where caused by memory leak in UploadLivestreamSegments
        $this->runAction(ManageWebsocketsConnection::class);

        $this->runAction(SendLogToMothership::class);
        $this->runAction(SendTemperatureLogToMothership::class);

        // 2024-09-09 Disable and run separately
        // $this->runAction(SendReportablesToMothership::class);
    }

    private function runAction($action)
    {
        try {
            app($action)->execute();
        } catch (\Exception $exception) {
            if (! $exception instanceof ConnectionException) {
                report($exception);
            }
        }
    }
}
