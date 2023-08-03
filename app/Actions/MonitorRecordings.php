<?php

namespace App\Actions;

use App\Models\Recording;
use App\Support\Recorder;

class MonitorRecordings
{
    public function execute()
    {
        foreach(Recording::running()->get() as $recording) {
            // Handle hard recorder shutoff without stopping recording before
            if(Recorder::make()->getUptime() < $recording->getDuration()) {
                $recording->camera->stopRecording();
            }

            // Abort recordings when camera is not available anymore
            if(!$recording->camera->type->isAvailable($recording->camera)) {
                $recording->camera->stopRecording();
                $recording->update([
                    'aborted_at' => now(),
                ]);
            }
        }

        // Restart recordings that have been aborted recently
        foreach(Recording::freshlyAborted()->get() as $recording) {
            if($recording->camera->type->isAvailable($recording->camera)) {
                $recording->restart();
            }
        }
    }
}
