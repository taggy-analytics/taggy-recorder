<?php

namespace App\Console\Commands;

use App\Models\LivestreamSegment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\Watcher\Watch;

class WatchRecordingSegments extends Command
{
    protected $signature = 'taggy:watch-recording-segments';

    protected $description = 'Watch recording segments';

    public const STOP_FILE_NAME = '.stopWatchRecordingSegments';

    public function handle()
    {
        $paths = collect(File::directories(Storage::disk("public")->path("recordings")))
            ->filter(
                fn($dir) => Carbon::createFromTimestamp(
                    File::lastModified($dir)
                )->greaterThanOrEqualTo(now()->subDay())
            )
            ->values()
            ->toArray();

        Storage::delete(self::STOP_FILE_NAME);

        Watch::paths($paths)
            ->shouldContinue(fn() => !Storage::exists(self::STOP_FILE_NAME))
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
