<?php

namespace App\Actions\Mothership;

use App\Enums\RecordingFileStatus;
use App\Models\RecordingFile;

class ReportRecordingFile extends Report
{
    public function execute(RecordingFile $recordingFile): bool
    {
        $this->mothership->sendRecordingFile($recordingFile);
        $recordingFile->setStatus(RecordingFileStatus::UPLOADED);

        return true;
    }
}
