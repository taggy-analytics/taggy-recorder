<?php

namespace App\Actions\Mothership;

use App\Enums\RecordingStatus;
use App\Models\Recording;
use App\Support\Mothership;
use Illuminate\Support\Facades\Storage;

class ReportRecordingsToMothership
{
    public function execute()
    {
        $mothership = Mothership::make();

        foreach(Recording::withStatus(RecordingStatus::CREATED_RECORDING_FILES_IN_DB) as $recording) {
            if($video = $mothership->reportRecording($recording)) {
                $recording->files()->update(['video_id' => $video['id']]);
                $playlist = Storage::disk('public')
                    ->get($recording->getPath('video/video.m3u8'));
                $mothership->sendPlaylist($video['id'], $playlist);
                $recording->setStatus(RecordingStatus::REPORTED_TO_MOTHERSHIP);
            }
        }
    }
}
