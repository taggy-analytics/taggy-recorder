<?php

namespace App\Actions\Mothership;

use App\Enums\MothershipReportStatus;
use App\Enums\RecordingFileStatus;
use App\Exceptions\MothershipException;
use App\Models\RecordingFile;

class ReportRecordingFile extends Report
{
    public function executeReport(RecordingFile $recordingFile): bool
    {
        if ($recordingFile->status == RecordingFileStatus::TO_BE_UPLOADED) {
            $remainingFiles = $recordingFile->recording->files()
                ->where('status', RecordingFileStatus::TO_BE_UPLOADED)
                ->count();

            try {
                $this->mothership->sendRecordingFile($recordingFile, $remainingFiles);
            } catch (MothershipException $exception) {
                if ($exception->response->status() == 404) {
                    // Video was deleted already - let's quickly process all "siblings"
                    $siblings = $recordingFile->recording->files;

                    foreach ($siblings as $sibling) {
                        $sibling->mothershipReport->update([
                            'status' => MothershipReportStatus::VideoNotAvailableAnymore,
                            'processed_at' => now(),
                        ]);
                        $sibling->update([
                            'status' => RecordingFileStatus::VIDEO_NOT_AVAILABLE_ANYMORE,
                        ]);
                    }
                } else {
                    throw $exception;
                }

                return false;
            }
        }

        $recordingFile->setStatus(RecordingFileStatus::UPLOADED);

        return true;
    }
}
