<?php

namespace App\Actions;

use App\Models\Recording;
use App\Support\FFMpegCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateSceneVideo
{
    public function execute(Recording $recording, $startTime, $duration)
    {
        $filename = $recording->sceneFilename($startTime, $duration);
        $m3u8Path = $this->m3u8PathWithEndTag($recording, $filename);

        File::ensureDirectoryExists(Storage::path('scenes'));

        $command = [
            '-ss', FFMpegCommand::convertSeconds($startTime - 3),
            '-start_at_zero',
            '-i', Storage::disk('public')->path($m3u8Path),
            '-ss', 3,
            '-t', FFMpegCommand::convertSeconds($duration),
            '-c', 'copy',
            '-f', 'mp4',
            Storage::path('scenes/' . $filename),
        ];

        FFMpegCommand::runRaw(implode(' ', $command), async: false);

        Storage::disk('public')->delete($m3u8Path);

        // ToDo: push video available event to clients
    }

    private function ensureScenesDirectoryExists() {}

    private function m3u8PathWithEndTag(Recording $recording, $filename)
    {
        // FFmpeg doesn't like it if live HLS streams' m3u8s are used. So let's copy it first.
        $m3u8Path = $recording->getPath('video/' . $filename . '.m3u8');

        Storage::disk('public')
            ->copy($recording->getM3u8Path(), $m3u8Path);

        if (! Str::contains(Storage::disk('public')->get($m3u8Path), '#EXT-X-ENDLIST')) {
            Storage::disk('public')->append($m3u8Path, PHP_EOL . '#EXT-X-ENDLIST');
        }

        return $m3u8Path;
    }
}
