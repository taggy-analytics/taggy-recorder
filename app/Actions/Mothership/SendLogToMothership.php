<?php

namespace App\Actions\Mothership;

use App\Enums\RecordingFileStatus;
use App\Enums\RecordingStatus;
use App\Models\RecorderLog;
use App\Models\Recording;
use App\Models\RecordingFile;
use App\Support\Mothership;
use Illuminate\Support\Arr;

class SendLogToMothership
{
    public function execute()
    {
        $mothership = Mothership::make();

        foreach(RecorderLog::whereNull('reported_at')->get() as $logEntry) {
            $response = $mothership->log([
                'occurred_at' => $logEntry->created_at->toDateTimeString(),
                'type' => $logEntry->type->value,
                'message' => $logEntry->message,
                'data' => $logEntry->data,
            ]);

            if($response == 'OK') {
                $logEntry->update([
                    'reported_at' => now(),
                ]);
            }
        }

        RecorderLog::query()
            ->whereNotNull('reported_at')
            ->where('reported_at', '<', now()->subDay())
            ->delete();
    }
}
