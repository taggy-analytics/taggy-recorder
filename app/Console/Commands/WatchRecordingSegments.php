<?php

namespace App\Console\Commands;

use App\Models\LivestreamSegment;
use Illuminate\Console\Command;
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
                $this->sendFile($newFilePath);
            })
            ->start();
    }

    private function sendFile($newFilePath)
    {
        LivestreamSegment::create([
            'file' => $newFilePath,
        ]);
    }
}
