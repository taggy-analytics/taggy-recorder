<?php

namespace App\Actions\Mothership;

use App\Enums\RecordingFileStatus;
use App\Models\RecordingFile;
use Spatie\QueueableAction\QueueableAction;

class ReportRecordingFile extends Report
{
    use QueueableAction;

    public function executeReport(RecordingFile $recordingFile): bool
    {
        $this->mothership->sendRecordingFile($recordingFile);
        $recordingFile->setStatus(RecordingFileStatus::UPLOADED);

        return true;
    }
}
