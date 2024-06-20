<?php

namespace App\Console\Commands;

use App\Models\LivestreamSegment;
use App\Models\Recording;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\Watcher\Watch;

class WatchRecordingSegments extends Command
{
    protected $signature = 'taggy:watch-recording-segments {recording}';

    protected $description = 'Watch recording segments';

    public function handle()
    {
        $recording = Recording::findOrFail($this->argument('recording'));
        $path = Storage::disk('public')->path($recording->getPath('video'));
        $startTime = now();

        Watch::path($path)
            ->shouldContinue(fn() => $startTime->diffInSeconds() < 10 || $recording->isRecordingProcessRunning())
            ->onFileCreated(function (string $newFilePath) {
                $m3u8Path = preg_replace('/video-\d+\.ts$/', 'video.m3u8', $newFilePath);
                $this->sendFile($newFilePath, m3u8Content: base64_encode(File::get($m3u8Path)));
            })
            ->start();
    }

    private function sendFile($newFilePath, $content = null, $m3u8Content = null)
    {
        LivestreamSegment::create([
            'file' => $newFilePath,
            'm3u8_content' => $m3u8Content,
            'content' => $content,
        ]);
    }
}
