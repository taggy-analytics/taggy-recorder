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
            if($recording->getDuration() && Recorder::make()->getUptime() < $recording->getDuration()) {
                info('Handle hard recorder shutoff');
                $recording->camera->stopRecording();
                $recording->cleanup();
            }

            // Abort recordings when camera is not available anymore
            if(!$this->cameraIsAvailable($recording)) {
                info('Handle camera not available anymore');
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
                info('Restart aborted recording');
                $recording->restart();
            }
        }
    }

    private function cameraIsAvailable(Recording $recording)
    {
        return $recording->camera->getType()->isAvailable($recording->camera);
    }
}
