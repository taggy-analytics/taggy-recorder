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
                $recording->cleanup();
            }

            // Abort recordings when camera is not available anymore
            if(!$this->cameraIsAvailable($recording)) {
                $recording->camera->stopRecording();
                $recording->update([
                    'aborted_at' => now(),
                ]);
                $recording->cleanup();
            }
        }

        // Restart recordings that have been aborted recently
        foreach(Recording::freshlyAborted()->get() as $recording) {
            if($this->cameraIsAvailable($recording)) {
                $recording->restart();
            }
        }
    }

    private function cameraIsAvailable(Recording $recording)
    {
        return $recording->camera->getType()->isAvailable($recording->camera);
    }
}
