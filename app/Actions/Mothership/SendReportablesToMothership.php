<?php

namespace App\Actions\Mothership;

use App\Enums\RecordingFileStatus;
use App\Enums\RecordingStatus;
use App\Models\MothershipReport;
use App\Models\Recording;
use App\Models\Scene;
use App\Support\Mothership;
use Illuminate\Support\Facades\Storage;

class SendReportablesToMothership
{
    public function execute()
    {
        foreach(MothershipReport::unreported() as $mothershipReport) {
            if($mothershipReport->model->sendToMothership()) {
                $mothershipReport->update(['reported_at' => now()]);
            }
        }
    }
}
