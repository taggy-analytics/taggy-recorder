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

        Watch::path($path)
            ->shouldContinue(fn() => $recording->isRecordingProcessRunning())
            ->onFileCreated(function (string $newFilePath) {
                $this->sendFile($newFilePath);
            })
            ->onFileUpdated(function (string $newFilePath) {
                $this->sendFile($newFilePath, true);
            })
            ->start();
    }

    private function sendFile($newFilePath, $withContent = false)
    {
        LivestreamSegment::create([
            'file' => $newFilePath,
            'content' => $withContent ? base64_encode(File::get($newFilePath)) : null,
        ]);
    }
}
