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
        if(!Arr::has($recording->data, 'assigned_container.eid')) {
            info('Recording #' . $recording->id . ' has no entity ID.');
            return false;
        }

        if($video = $this->mothership->reportRecording($recording)) {
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
                    'user_token' => $recording->mothershipReport->getAttributes()['user_token'],
                    'updated_at' => $currentTime,
                ]);

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
