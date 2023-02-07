<?php

namespace App\Actions;

use App\Actions\Mothership\GetCredentialsForUnauthenticatedCameras;
use App\Actions\Mothership\SendDiscoveredCamerasToMothership;
use App\CameraTypes\CameraType;
use App\Enums\RecordingFileType;
use App\Models\Camera;
use App\Models\Recording;
use App\Support\Uploader;
use Illuminate\Support\Str;
use Spatie\Watcher\Watch;

class WatchRecordedFiles
{
    public function execute()
    {
        $basePath = storage_path("app/cameras");

        Watch::path($basePath)
            ->onAnyChange(function (string $type, string $path) use ($basePath) {
                if ($type === Watch::EVENT_TYPE_FILE_CREATED) {
                    $recording = $this->getRecording($path);
                    $recording->files()->create([
                        'name' => $this->getPathInfo($path)['fileName'],
                        'path' => Str::replaceFirst($basePath, '', $path),
                        'type' => Str::endsWith($path, '.m3u8') ? RecordingFileType::PLAYLIST : RecordingFileType::VIDEO_TS,
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
