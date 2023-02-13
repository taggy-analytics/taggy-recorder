<?php

namespace App\Actions;

use App\Enums\RecordingStatus;
use App\Models\Camera;
use App\Models\Recording;

class HandleRecordings
{
    public function execute()
    {
        foreach(Recording::where('status', RecordingStatus::CREATED)->get() as $recording) {
            if(Camera::noCameraIsRecording()) {
                app(PreprocessRecording::class)
                    ->onQueue()
                    ->execute($recording);
            }
        }
    }
}
