<?php

namespace App\Actions\Mothership;

use App\Enums\RecordingFileStatus;
use App\Enums\RecordingStatus;
use App\Models\MothershipReport;
use App\Models\Recording;
use App\Models\RecordingFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

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

        if($video = $this->mothership->reportRecording($recording)) {
            if($video == RecordingStatus::SESSION_NOT_FOUND_ON_MOTHERSHIP) {
                $recording->setStatus(RecordingStatus::SESSION_NOT_FOUND_ON_MOTHERSHIP);
            }
            elseif($video == RecordingStatus::RECORDER_NOT_FOUND_ON_MOTHERSHIP) {
                $recording->setStatus(RecordingStatus::RECORDER_NOT_FOUND_ON_MOTHERSHIP);
            }
            elseif($video == RecordingStatus::UNKNOWN_MOTHERSHIP_ERROR) {
                $recording->setStatus(RecordingStatus::UNKNOWN_MOTHERSHIP_ERROR);
            }
            else {
                $recording->files()->update([
                    'video_id' => $video['id'],
                    'status' => RecordingFileStatus::TO_BE_UPLOADED,
                ]);

                $currentTime = now()->toDateTimeString();

                MothershipReport::query()
                    ->where('model_type', RecordingFile::class)
                    ->whereIn('model_id', $recording->files()->pluck('id'))
                    ->update([
                        'ready_to_send' => true,
                        'user_token_id' => $recording->mothershipReport->user_token_id,
                        'updated_at' => $currentTime,
                    ]);

                $recording->addM3u8EndTag();
                $playlist = Storage::disk('public')
                    ->get($recording->getPath('video/video.m3u8'));
                $this->mothership->sendPlaylist($video['id'], $playlist);
                $recording->setStatus(RecordingStatus::REPORTED_TO_MOTHERSHIP);
            }

            return true;
        }

        return false;
    }
}
