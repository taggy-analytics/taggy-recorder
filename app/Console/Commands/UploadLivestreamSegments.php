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
            LivestreamSegment::where('uploaded_at', '<', now()->subMinutes(5))->delete();

            LivestreamSegment::query()
                ->whereNull('uploaded_at')
                ->orderBy('last_failed_at')
                ->take(5)
                ->get()
                ->each(fn($livestreamSegment) => $this->sendFile($livestreamSegment));

            sleep(1);
        }
    }

    private function sendFile(LivestreamSegment $segment)
    {
        try {
            $recording = $segment->getRecording();
            if($recording->livestream_enabled) {
                $userToken = UserToken::forEndpointAndEntity($recording->data['endpoint'], $recording->data['entity_id']);
                Mothership::make($userToken)->sendLivestreamFile($recording, $segment->file, $segment->content, $segment->m3u8_content);
                $segment->update(['uploaded_at' => now()]);
            }
        }
        catch(\Exception $exception) {
            $segment->update(['last_failed_at' => now()]);
            exit;
        }

    }
}
