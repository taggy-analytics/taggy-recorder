<?php

namespace App\Actions\Mothership;

use App\Models\MothershipReport;

class SendReportablesToMothership
{
    public function execute()
    {
        foreach(MothershipReport::unreported() as $mothershipReport) {
            $actionClass = 'App\\Actions\\Mothership\\Report' . (new \ReflectionClass($mothershipReport->model))->getShortName();
            $mothershipReport->update(['reported_at' => now()]);
            app($actionClass)->execute($mothershipReport->model);
        }
    }
}
