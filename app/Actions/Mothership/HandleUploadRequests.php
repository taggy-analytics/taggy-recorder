<?php

namespace App\Actions\Mothership;

use App\Enums\RecordingFileStatus;
use App\Models\Recording;
use App\Models\RecordingFile;
use App\Support\Mothership;
use Illuminate\Support\Arr;

class HandleUploadRequests
{
    public function execute()
    {
        $mothership = Mothership::make();

        foreach($mothership->getUploadRecordingRequests() as $uploadRecordingRequest) {
            $recording = Recording::find($uploadRecordingRequest['recording']['remote_id']);
            $start = floor($uploadRecordingRequest['range'][0] * $recording->files->count());
            $end = ceil($uploadRecordingRequest['range'][1] * $recording->files->count());
            $totalVideoDuration = ($end - $start + 1) * config('taggy-recorder.video-conversion.segment-duration');

            $fileIdsToUpload = $recording->files->slice($start, $end - $start)->pluck('id');

            $numberOfFilesToUpload = RecordingFile::query()
                ->whereIn('id', $fileIdsToUpload)
                ->whereNot('status', RecordingFileStatus::UPLOADED)
                ->update([
                    'status' => RecordingFileStatus::TO_BE_UPLOADED,
                    'video_id' => $uploadRecordingRequest['video_id'],
                ]);

            $thumbnail = RecordingFile::find(Arr::first($fileIdsToUpload))->thumbnailsPath();

            $mothership->confirmRecordingUploadRequest(
                $uploadRecordingRequest['video_id'],
                $numberOfFilesToUpload,
                $thumbnail,
                $totalVideoDuration,
            );
        }
    }
}
