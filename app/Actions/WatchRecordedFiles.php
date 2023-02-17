<?php

namespace App\Actions;

use App\Enums\RecordingFileType;
use App\Models\Recording;
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
                    $this->getRecording($path)->files()->create([
                        'name' => $this->getPathInfo($path)['fileName'],
                        'type' => Str::endsWith($path, '.m3u8') ? RecordingFileType::PLAYLIST : RecordingFileType::VIDEO_TS,
                    ]);
                }
            })
            ->start();
    }

    private function getRecording($path)
    {
        return Recording::find($this->getPathInfo($path)['recordingId']);
    }

    private function getPathInfo($path)
    {
        $info = explode('/', $path);

        return [
            'cameraId' => $info[9],
            'recordingId' => $info[11],
            'fileName' => $info[13],
        ];
    }
}
