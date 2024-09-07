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
        while(count(MothershipReport::unreported()) > 0) {
            $errored = false;

            foreach(MothershipReport::unreported() as $mothershipReport) {
                if(Recorder::make()->isLivestreaming()) {
                    return;
                }

                Flare::context('motherShipReport', $mothershipReport);
                if(!Recorder::make()->isUploading()) {
                    Recorder::make()->isUploading(true);
                }
                if(!$mothershipReport->model) {
                    $mothershipReport->delete();
                    continue;
                }
                $actionClass = 'App\\Actions\\Mothership\\Report' . (new \ReflectionClass($mothershipReport->model))->getShortName();
                $mothershipReport->update(['reported_at' => now()]);
                if(!app($actionClass)->execute($mothershipReport->model)) {
                    $errored = true;
                }
            }

            if($errored) {
                info('Sleeping a little because of error while reporting to mothership...');
                sleep(10);
            }
        }

        if(Recorder::make()->isUploading()) {
            Recorder::make()->isUploading(false);
        }
    }
}
