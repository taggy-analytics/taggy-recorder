<?php

namespace App\Actions\Mothership;

use App\Enums\RecordingFileStatus;
use App\Models\RecordingFile;

class ReportRecordingFile extends Report
{
    public function executeReport(RecordingFile $recordingFile): bool
    {
        if($recordingFile->status == RecordingFileStatus::TO_BE_UPLOADED) {
            $this->mothership->sendRecordingFile($recordingFile);
        }

        $recordingFile->setStatus(RecordingFileStatus::UPLOADED);

        return true;
    }
}
