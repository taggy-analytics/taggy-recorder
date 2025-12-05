<?php

namespace App\Console\Commands;

use App\Models\MothershipReport;
use App\Support\Mothership;
use App\Support\Recorder;
use Illuminate\Console\Command;
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

            if (! $recorder->inProMode()) {
                info('UploadRecordings: not in pro Mode.');
                sleep(60);

                continue;
            }

            if (! Mothership::make()->isOnline(disableCache: true)) {
                sleep(10);

                continue;
            }

            $unreportedReports = MothershipReport::unreported();

            if ($unreportedReports->count() == 0) {
                if ($recorder->isUploading()) {
                    $recorder->isUploading(false);
                }
                sleep(10);

                continue;
            }

            $errored = false;

            foreach ($unreportedReports as $mothershipReport) {
                if ($recorder->isLivestreaming()) {
                    sleep(10);

                    continue;
                }

                Flare::context('mothershipReport', $mothershipReport);
                if (! $recorder->isUploading()) {
                    $recorder->isUploading(true);
                }
                if (! $mothershipReport->model) {
                    $mothershipReport->delete();

                    continue;
                }
                $actionClass = 'App\\Actions\\Mothership\\Report' . (new \ReflectionClass($mothershipReport->model))->getShortName();
                $mothershipReport->update(['reported_at' => now()]);
                if (! app($actionClass)->execute($mothershipReport->model)) {
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
