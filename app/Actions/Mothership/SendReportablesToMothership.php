<?php

namespace App\Actions\Mothership;

use App\Actions\CalculateLed;
use App\Models\Camera;
use App\Models\MothershipReport;
use App\Support\Recorder;
use Spatie\LaravelIgnition\Facades\Flare;

class SendReportablesToMothership
{
    public function execute()
    {
        $unreportedReports = MothershipReport::unreported();
        static $reflectionCache = [];
        $recorder = Recorder::make();

        while(count($unreportedReports) > 0) {
            $errored = false;

            foreach($unreportedReports as $mothershipReport) {
                if($recorder->isLivestreaming()) {
                    return;
                }

                // preventMemoryLeak();

                Flare::context('motherShipReport', $mothershipReport);
                if(!$recorder->isUploading()) {
                    $recorder->isUploading(true);
                }
                if(!$mothershipReport->model) {
                    $mothershipReport->delete();
                    $mothershipReport = null;
                    continue;
                }

                $modelClass = get_class($mothershipReport->model);
                if (!isset($reflectionCache[$modelClass])) {
                    $reflectionCache[$modelClass] = new \ReflectionClass($modelClass);
                }
                $actionClass = 'App\\Actions\\Mothership\\Report' . $reflectionCache[$modelClass]->getShortName();

                $mothershipReport->update(['reported_at' => now()]);
                if(!app($actionClass)->execute($mothershipReport->model)) {
                    $errored = true;
                }
            }

            if($errored) {
                info('Sleeping a little because of error while reporting to mothership...');
                sleep(10);
            }

            $unreportedReports = MothershipReport::unreported();
        }

        if($recorder->isUploading()) {
            $recorder->isUploading(false);
        }
    }
}
