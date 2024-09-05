<?php

namespace App\Actions;

use App\Enums\RecordingFileStatus;
use App\Enums\RecordingStatus;
use App\Models\RecordingFile;
use App\Support\Recorder;
use Illuminate\Support\Facades\Process;
use Spatie\Regex\Regex;

class FreeDiskSpace
{
    private $startDeletingOldFilesAt = 20;
    private $deleteOldFilesUntil = 50;

    public function execute()
    {
        if($this->getDiskUsage() < $this->startDeletingOldFilesAt) {
            $filesToDelete = RecordingFile::query()
                ->with('recording')
                ->where('status', RecordingFileStatus::UPLOADED)
                ->orderBy('updated_at')
                ->get();

            while ($this->getDiskUsage() < $this->deleteOldFilesUntil && $filesToDelete->count() > 0) {
                $fileToDelete = $filesToDelete->shift();
                $fileToDelete?->delete();
                $fileToDelete?->recording->update([
                    'status' => RecordingStatus::DELETING_FILES,
                ]);
                sleep(1);
            }
        }
    }

    private function getDiskUsage()
    {
        return disk_free_space('/') / 1024 / 1024 / 1024;
    }
}
