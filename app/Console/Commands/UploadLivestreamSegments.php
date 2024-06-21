<?php

namespace App\Console\Commands;

use App\Models\LivestreamSegment;
use App\Models\Recording;
use App\Models\UserToken;
use App\Support\Mothership;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class UploadLivestreamSegments extends Command
{
    protected $signature = 'taggy:upload-livestream-segments';

    protected $description = 'Upload livestream segments';

    public function handle()
    {
        while(true) {
            try {
                LivestreamSegment::where('uploaded_at', '<', now()->subMinutes(5))->delete();

                LivestreamSegment::query()
                    ->whereNull('uploaded_at')
                    ->orderBy('id')
                    ->take(5)
                    ->get()
                    ->each(fn($livestreamSegment) => $this->sendFile($livestreamSegment));

                sleep(1);
            }
            catch(\Exception $exception) {
                sleep(10);
            }
        }
    }

    private function sendFile(LivestreamSegment $segment)
    {
        try {
            $recording = $segment->getRecording();
            if($recording->livestream_enabled) {
                $userToken = UserToken::forEndpointAndEntity($recording->data['endpoint'], $recording->data['entity_id']);
                $m3u8Content = Storage::get('segment-m3u8-' . $segment->id);
                Storage::delete('segment-m3u8-' . $segment->id);
                Mothership::make($userToken)->sendLivestreamFile($recording, $segment->file, $segment->content, $m3u8Content);
                $segment->update(['uploaded_at' => now()]);
            }
        }
        catch(\Exception $exception) {
            $segment->update(['last_failed_at' => now()]);
            exit;
        }
    }
}
