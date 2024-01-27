<?php

namespace App\Console\Commands;

use App\Models\LivestreamSegment;
use App\Models\Recording;
use App\Models\UserToken;
use App\Support\Mothership;
use Illuminate\Console\Command;

class UploadLivestreamSegments extends Command
{
    protected $signature = 'taggy:upload-livestream-segments';

    protected $description = 'Upload livestream segments';

    public function handle()
    {
        while(true) {
            LivestreamSegment::whereNull('uploaded_at')
                ->get()
                ->each(fn(LivestreamSegment $livestreamSegment) => $this->sendFile($livestreamSegment->file));

            usleep(500000);
        }
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
