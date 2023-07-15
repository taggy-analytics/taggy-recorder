<?php

namespace App\Actions\Mothership;

use App\Enums\RecordingFileStatus;
use App\Enums\RecordingStatus;
use App\Models\Recording;
use App\Models\RecordingFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class ReportRecording extends Report
{
    public function executeReport(Recording $recording): bool
    {
        if(Arr::has($recording->data, 'eid')) {
            info('Recording #' . $recording->id . ' has no entity ID.');
            return false;
        }

        if($video = $this->mothership->reportRecording($recording)) {
            $recording->files()->update([
                'video_id' => $video['id'],
                'status' => RecordingFileStatus::TO_BE_UPLOADED,
            ]);
            $recording->files()->each(fn(RecordingFile $file) => $file->reportToMothership($recording->mothershipReport->user_token));
            $recording->addM3u8EndTag();
            $playlist = Storage::disk('public')
                ->get($recording->getPath('video/video.m3u8'));
            $this->mothership->sendPlaylist($video['id'], $playlist);
            $recording->setStatus(RecordingStatus::REPORTED_TO_MOTHERSHIP);

            return true;
        }

        return false;
    }
}
