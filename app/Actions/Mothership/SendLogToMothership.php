<?php

namespace App\Actions\Mothership;

use App\Exceptions\MothershipException;
use App\Models\RecorderLog;
use App\Support\Mothership;

class SendLogToMothership
{
    public function execute()
    {
        $mothership = Mothership::make(endpoint: config('services.mothership.production.endpoint'));

        try {
            foreach (RecorderLog::whereNull('reported_at')->get() as $logEntry) {
                $response = $mothership->log([
                    'occurred_at' => $logEntry->created_at->toDateTimeString(),
                    'type' => $logEntry->type->value,
                    'message' => $logEntry->message,
                    'data' => $logEntry->data,
                ]);

                if ($response == 'OK') {
                    $logEntry->update([
                        'reported_at' => now(),
                    ]);
                }
            }
        } catch (MothershipException $exception) {

        }

        RecorderLog::query()
            ->whereNotNull('reported_at')
            ->where('reported_at', '<', now()->subDay())
            ->delete();
    }
}
