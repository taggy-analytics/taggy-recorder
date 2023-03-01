<?php

namespace App\Actions\Mothership;

use App\Enums\RecordingFileStatus;
use App\Enums\RecordingStatus;
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
            $recording = Recording::find($deleteRecordingRequest['remote_id']);
            $recording->setStatus(RecordingStatus::TO_BE_DELETED);
            $mothership->confirmDeleteRequest($recording);
        }
    }
}
