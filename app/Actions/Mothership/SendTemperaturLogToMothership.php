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
        $logfile = 'logs/temperature.log';

        $mothership = Mothership::make(endpoint: config('services.mothership.production.endpoint'));

        if(Storage::exists($logfile) && Storage::size($logfile) >= config('taggy-recorder.temperature-log-min-size')) {
            $status = $mothership->sendTemperatureLog(Storage::get($logfile));
            if($status == 'OK') {
                Storage::delete($logfile);
            }
        }
    }
}
