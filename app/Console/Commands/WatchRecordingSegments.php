<?php

namespace App\Console\Commands;

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
                $this->info("File created: {$newFilePath}");
            })
            ->onFileUpdated(function (string $newFilePath) {
                // m3u8
                $this->info("File updated: {$newFilePath}");
            })
            ->start();
    }
}
