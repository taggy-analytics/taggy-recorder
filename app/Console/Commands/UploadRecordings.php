<?php

namespace App\Console\Commands;

use App\Models\Camera;
use App\Models\LivestreamSegment;
use App\Models\MothershipReport;
use App\Models\UserToken;
use App\Support\Mothership;
use App\Support\Recorder;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelIgnition\Facades\Flare;

class UploadRecordings extends Command
{
    protected $signature = 'taggy:upload-recording-segments';

    protected $description = 'Upload recordings';

    public function handle()
    {
        $recorder = Recorder::make();
        $recorder->waitUntilAllNeededServicesAreUpAndRunning();

        while (true) {
            preventMemoryLeak('UploadRecordingSegments');

            if(!Mothership::make()->isOnline(disableCache: true)) {
                sleep(10);
                continue;
            }

            $errored = false;

            $unreportedReports = MothershipReport::unreported();

            if ($unreportedReports->count() == 0 && $recorder->isUploading()) {
                $recorder->isUploading(false);
            }

            foreach ($unreportedReports as $mothershipReport) {
                if ($recorder->isLivestreaming()) {
                    sleep(10);
                    continue;
                }

                Flare::context('mothershipReport', $mothershipReport);
                if (!$recorder->isUploading()) {
                    $recorder->isUploading(true);
                }
                if (!$mothershipReport->model) {
                    $mothershipReport->delete();
                    continue;
                }
                $actionClass = 'App\\Actions\\Mothership\\Report' . (new \ReflectionClass($mothershipReport->model))->getShortName();
                $mothershipReport->update(['reported_at' => now()]);
                if (!app($actionClass)->execute($mothershipReport->model)) {
                    $errored = true;
                }
            }

            if ($errored) {
                info('Sleeping a little because of error while reporting to mothership...');
                sleep(10);
            }
        }
    }
}
