<?php

namespace App\Actions;

use App\Actions\Mothership\GetCredentialsForUnauthenticatedCameras;
use App\Actions\Mothership\SendDiscoveredCamerasToMothership;
use App\CameraTypes\CameraType;
use App\Enums\RecordingFileType;
use App\Enums\RecordingStatus;
use App\Models\Camera;
use App\Models\Recording;
use App\Support\Uploader;
use Illuminate\Support\Str;
use Spatie\Watcher\Watch;

class HandleRecordings
{
    public function execute()
    {
        return;

        foreach(Recording::where('status', RecordingStatus::CREATED)->get() as $recording) {
            if(!$recording->isRecording()) {
                app(PreprocessRecording::class)
                    ->onQueue()
                    ->execute($recording);
            }
        }
    }
}
