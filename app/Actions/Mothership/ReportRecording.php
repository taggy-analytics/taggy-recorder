<?php

namespace App\Actions\Mothership;

use App\Enums\RecordingFileStatus;
use App\Enums\RecordingFileType;
use App\Enums\RecordingStatus;
use App\Models\MothershipReport;
use App\Models\Recording;
use App\Models\RecordingFile;

class ReportRecording extends Report
{
    public function executeReport(Recording $recording): bool
    {
        // Just to make sure that the mothership knows about the container,
        // we will sync the transactions. It should not take too long
        // because they should already have been synced when the websocket
        // reconnected (echo.js: php ./artisan taggy:handle-mothership-websockets-event).
        // Maybe we have an issue when two syncs run in parallel?! Should not hurt.
        app(SyncTransactionsWithMothership::class)->execute();

        if ($toVideoResponse = $this->mothership->reportRecording($recording)) {
            if ($toVideoResponse == RecordingStatus::SESSION_NOT_FOUND_ON_MOTHERSHIP) {
                $recording->setStatus(RecordingStatus::SESSION_NOT_FOUND_ON_MOTHERSHIP);
            } elseif ($toVideoResponse == RecordingStatus::RECORDER_NOT_FOUND_ON_MOTHERSHIP) {
                $recording->setStatus(RecordingStatus::RECORDER_NOT_FOUND_ON_MOTHERSHIP);
            } elseif ($toVideoResponse == RecordingStatus::UNKNOWN_MOTHERSHIP_ERROR) {
                $recording->setStatus(RecordingStatus::UNKNOWN_MOTHERSHIP_ERROR);
            } else {
                $livestreamedFiles = $recording
                    ->files
                    ->whereIn('name', $toVideoResponse['knownFiles'])
                    ->where('type', '!=', RecordingFileType::PLAYLIST)
                    ->pluck('id');

                $recording->files()->whereIn('id', $livestreamedFiles)->update([
                    'video_id' => $toVideoResponse['video']['id'],
                    'video_format_id' => $toVideoResponse['videoFormat']['id'],
                    'status' => RecordingFileStatus::UPLOADED,
                ]);

                $recording->files()->whereNotIn('id', $livestreamedFiles)->update([
                    'video_id' => $toVideoResponse['video']['id'],
                    'video_format_id' => $toVideoResponse['videoFormat']['id'],
                    'status' => RecordingFileStatus::TO_BE_UPLOADED,
                ]);

                MothershipReport::query()
                    ->where('model_type', RecordingFile::class)
                    ->whereIn('model_id', $livestreamedFiles)
                    ->delete();

                MothershipReport::query()
                    ->where('model_type', RecordingFile::class)
                    ->whereIn('model_id', $recording->files()->pluck('id'))
                    ->update([
                        'ready_to_send' => true,
                        'user_token_id' => $recording->mothershipReport->user_token_id,
                        'updated_at' => now()->toDateTimeString(),
                    ]);

                $recording->addM3u8EndTag();
                $recording->setStatus(RecordingStatus::REPORTED_TO_MOTHERSHIP);
            }

            return true;
        }

        return false;
    }
}
