<?php

namespace App\Console\Commands;

use App\Models\Recording;
use App\Models\UserToken;
use App\Support\Mothership;
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
        $recording = $this->getRecording($newFilePath);
        if($recording->livestream_enabled) {
            $userToken = UserToken::forEndpointAndEntity($recording->data['endpoint'], $recording->data['entity_id']);
            Mothership::make($userToken)->sendLivestreamFile($recording, $newFilePath);
        }
    }

    private function getRecording($filePath)
    {
        $recordingId = array_slice(explode("/", $filePath), -4, 1)[0];
        return Recording::find($recordingId);
    }
}
