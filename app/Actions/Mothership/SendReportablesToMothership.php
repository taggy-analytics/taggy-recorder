<?php

namespace App\Actions\Mothership;

use App\Actions\CalculateLed;
use App\Models\MothershipReport;
use App\Support\Recorder;

class SendReportablesToMothership
{
    public function execute()
    {
        while(count(MothershipReport::unreported()) > 0) {
            foreach(MothershipReport::unreported() as $mothershipReport) {
                if(!Recorder::make()->isUploading()) {
                    Recorder::make()->isUploading(true);
                }
                $actionClass = 'App\\Actions\\Mothership\\Report' . (new \ReflectionClass($mothershipReport->model))->getShortName();
                $mothershipReport->update(['reported_at' => now()]);
                app($actionClass)->execute($mothershipReport->model);
            }
        }

        if(Recorder::make()->isUploading()) {
            Recorder::make()->isUploading(false);
        }
    }
}
