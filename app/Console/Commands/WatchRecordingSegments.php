<?php

namespace App\Console\Commands;

use App\Models\LivestreamSegment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\Watcher\Watch;

class WatchRecordingSegments extends Command
{
    protected $signature = 'taggy:watch-recording-segments';

    protected $description = 'Watch recording segments';

    public function handle()
    {
        Watch::path(Storage::disk('public')->path('recordings'))
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
