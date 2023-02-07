<?php

namespace App\Actions;

use App\Actions\Mothership\GetCredentialsForUnauthenticatedCameras;
use App\Actions\Mothership\SendDiscoveredCamerasToMothership;
use App\CameraTypes\CameraType;
use App\Models\Camera;
use App\Models\Recording;
use App\Support\Uploader;
use Spatie\Watcher\Watch;

class WatchRecordedFiles
{
    public function execute()
    {
        Watch::path(storage_path("app/cameras"))
            ->onAnyChange(function (string $type, string $path) {
                if ($type === Watch::EVENT_TYPE_FILE_CREATED) {
                    $recording = $this->getRecording($path);
                    $recording->files()->create([
                        'name' => $this->getPathInfo($path)['fileName'],
                        'path' => $path,
                    ]);
                }
            })
            ->start();
    }

    private function getRecording($path)
    {
        return Recording::firstOrCreate([
            'camera_id' => $this->getPathInfo($path)['cameraId'],
            'name' => $this->getPathInfo($path)['recordingName'],
        ]);
    }

    private function getPathInfo($path)
    {
        $info = explode('/', $path);

        return [
            'cameraId' => $info[9],
            'recordingName' => $info[11],
            'fileName' => $info[12],
        ];
    }
}
