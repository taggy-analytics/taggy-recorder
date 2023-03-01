<?php

namespace App\Actions\Mothership;

use App\Enums\RecordingFileStatus;
use App\Models\Recording;
use App\Models\RecordingFile;
use App\Support\Mothership;
use Illuminate\Support\Arr;

class CheckForDeletedRecordings
{
    public function execute()
    {
        $mothership = Mothership::make();

        foreach($mothership->getDeleteRecordingRequests() as $deleteRecordingRequest) {
            $recording = Recording::find($deleteRecordingRequest['recording']['remote_id']);

            info($recording);
        }
    }
}
