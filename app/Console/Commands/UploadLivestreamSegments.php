<?php

namespace App\Console\Commands;

use App\Models\Camera;
use App\Models\LivestreamSegment;
use App\Models\UserToken;
use App\Support\Mothership;
use App\Support\Recorder;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadLivestreamSegments extends Command
{
    protected $signature = 'taggy:upload-livestream-segments';

    protected $description = 'Upload livestream segments';

    public function handle()
    {
        Recorder::make()->waitUntilAllNeededServicesAreUpAndRunning();

        while(true) {
            if(!Recorder::make()->inProMode()) {
                sleep(60);
                continue;
            }

            if(!Mothership::make()->isOnline(disableCache: true)) {
                sleep(10);
                continue;
            }

            // This script somehow has a memory leak which eventually causes a Pi crash.
            // As a workaround let's just kill it when it consumes more than 300MB.
            // The supervisor will start it right up again.
            preventMemoryLeak('loop');

            try {
                $livestreamSegments = LivestreamSegment::query()
                    ->whereNull('uploaded_at')
                    ->orderBy('id')
                    ->take(5)
                    ->get();

                if(count($livestreamSegments) > 0) {
                    $livestreamSegments->each(fn($livestreamSegment) => $this->sendFile($livestreamSegment));
                    sleep(1);
                }
                elseif(Camera::noCameraIsRecording()) {
                    sleep(10);
                }
                else {
                    sleep(1);
                }
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

            preventMemoryLeak('sendFile');
            Log::channel('memory')->info(memory_get_usage());

            if($recording->livestream_enabled && Arr::has($recording->data, ['endpoint'])) {
                $userToken = UserToken::forEndpointAndEntity($recording->data['endpoint'], $recording->data['entity_id']);
                $m3u8Content = explode(PHP_EOL, trim(Storage::get('segments-m3u8/segment-m3u8-' . $segment->id)));
                Storage::delete('segments-m3u8/segment-m3u8-' . $segment->id);
                Mothership::make($userToken)->sendLivestreamFile($recording, $segment->file, $segment->content, implode(PHP_EOL, array_slice($m3u8Content, 0, -2)));
                $segment->update(['uploaded_at' => now()]);
            }
        }
        catch(\Exception $exception) {
            $segment->update(['last_failed_at' => now()]);
            report($exception);
        }
    }
}
