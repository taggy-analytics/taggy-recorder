<?php

namespace App\Console\Commands;

use App\Models\LivestreamSegment;
use App\Models\UserToken;
use App\Support\Mothership;
use App\Support\Recorder;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class UploadLivestreamSegments extends Command
{
    protected $signature = 'taggy:upload-livestream-segments';

    protected $description = 'Upload livestream segments';

    public function handle()
    {
        Recorder::make()->waitUntilAllNeededServicesAreUpAndRunning();

        checkMemory('start', true);

        while(true) {
            if(!Mothership::make()->isOnline(disableCache: true)) {
                sleep(10);
                continue;
            }

            try {
                LivestreamSegment::query()
                    ->whereNull('uploaded_at')
                    ->orderBy('id')
                    ->take(5)
                    ->get()
                    ->each(fn($livestreamSegment) => $this->sendFile($livestreamSegment));

                checkMemory('afterBatch');

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
            checkMemory('afterRecording');
            if($recording->livestream_enabled && Arr::has($recording->data, ['endpoint'])) {
                $userToken = UserToken::forEndpointAndEntity($recording->data['endpoint'], $recording->data['entity_id']);
                checkMemory('afterUserToken');
                $m3u8Content = explode(PHP_EOL, trim(Storage::get('segments-m3u8/segment-m3u8-' . $segment->id)));
                checkMemory('afterM3u8Content');
                Storage::delete('segments-m3u8/segment-m3u8-' . $segment->id);
                checkMemory('afterStorageDelete');
                Mothership::make($userToken)->sendLivestreamFile($recording, $segment->file, $segment->content, implode(PHP_EOL, array_slice($m3u8Content, 0, -2)));
                checkMemory('afterSendLivestreamFile');
                $segment->update(['uploaded_at' => now()]);
                checkMemory('afterUpdate');
            }
        }
        catch(\Exception $exception) {
            $segment->update(['last_failed_at' => now()]);
            report($exception);
        }
    }
}
