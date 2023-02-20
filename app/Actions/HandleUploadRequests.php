<?php

namespace App\Actions;

use App\Enums\RecordingFileStatus;
use App\Models\Recording;
use App\Models\RecordingFile;
use App\Support\Mothership;

class HandleUploadRequests
{
    public function execute()
    {
        $mothership = Mothership::make();

        foreach($mothership->getUploadRecordingRequests() as $uploadRecordingRequest) {
            dump($uploadRecordingRequest);

            $recording = Recording::find($uploadRecordingRequest['recording']['remote_id']);
            $start = floor($uploadRecordingRequest['range'][0] * $recording->files->count());
            $end = ceil($uploadRecordingRequest['range'][1] * $recording->files->count());

            $fileIdsToUpload = $recording->files->slice($start, $end - $start)->pluck('id');

            $numberOfFilesToUpload = RecordingFile::query()
                ->whereIn('id', $fileIdsToUpload)
                ->whereNot('status', RecordingFileStatus::UPLOADED)
                ->update([
                    'status' => RecordingFileStatus::TO_BE_UPLOADED,
                    'video_id' => $uploadRecordingRequest['video_id'],
                ]);

            $mothership->confirmRecordingUploadRequest($uploadRecordingRequest['video_id'], $numberOfFilesToUpload);
        }
    }
}
