<?php

namespace App\Actions;

use App\Models\Recording;
use App\Models\Scene;
use App\Support\FFMpegCommand;
use Illuminate\Support\Facades\Storage;
use Spatie\QueueableAction\QueueableAction;

class CreateSceneVideo
{
    use QueueableAction;

    public function execute(Scene $scene, Recording $recording)
    {
        $filename = $scene->videoFilePath($recording);

        Storage::makeDirectory(dirname($filename));

        // ToDo: das muss besser gehen. Es muss sichergestellt werden, dass alle hls-Dateien für den Schnitt bereits vorhanden sind
        if($scene->getEndTime()->diffInSeconds() < 5) {
            sleep(5);
        }

        // FFmpeg doesn't like it if live HLS streams' m3u8s are used. So let's copy it first.
        $m3u8Path = $recording->getPath('video/video-' . $scene->id . '.m3u8');
        Storage::disk('public')
            ->copy($recording->getPath('video/video.m3u8'), $m3u8Path);
        Storage::disk('public')
            ->append($m3u8Path, PHP_EOL . '#EXT-X-ENDLIST');

        $command = [
            '-ss', FFMpegCommand::convertSeconds($scene->start_time->diffInMilliseconds($recording->started_at) / 1000),
            '-i', Storage::disk('public')->path($m3u8Path),
            '-t', FFMpegCommand::convertSeconds($scene->duration),
            '-c', 'copy',
            '-f', 'mp4',
            Storage::path($filename),
        ];

        FFMpegCommand::runRaw(implode(' ', $command), async: false);
        Storage::put($scene->videoFilePath($recording, 'ready'), '');

        // Storage::disk('public')->delete($m3u8Path);

        // ToDo: push video available event to clients
    }
}
