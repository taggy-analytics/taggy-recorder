<?php

namespace App\Actions\Mothership;

use App\Exceptions\MothershipException;
use App\Models\RecorderLog;
use App\Support\Mothership;
use Illuminate\Support\Facades\Storage;

class SendTemperaturLogToMothership
{
    public function execute()
    {
        $mothership = Mothership::make(endpoint: config('services.mothership.production.endpoint'));

        if(Storage::size('temperature.log') >= config('taggy-recorder.temperature-log-min-size')) {
            $status = $mothership->sendTemperatureLog(Storage::get('temperature.log'));
            if($status == 'OK') {
                Storage::delete('temperature.log');
            }
        }
    }
}
