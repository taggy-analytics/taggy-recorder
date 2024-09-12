<?php

namespace App\Actions\Mothership;

use App\Enums\RecordingFileStatus;
use App\Models\RecordingFile;

class ReportRecordingFile extends Report
{
    public function executeReport(RecordingFile $recordingFile): bool
    {
        if($recordingFile->status == RecordingFileStatus::TO_BE_UPLOADED) {
            $remainingFiles = $recordingFile->recording->files()
                ->where("status", RecordingFileStatus::TO_BE_UPLOADED)
                ->count();

            $this->mothership->sendRecordingFile($recordingFile, $remainingFiles);
        }

        $recordingFile->setStatus(RecordingFileStatus::UPLOADED);

        return true;
    }
}
