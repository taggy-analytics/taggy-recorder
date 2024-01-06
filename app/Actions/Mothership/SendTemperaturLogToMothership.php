<?php

namespace App\Actions\Mothership;

use App\Support\Mothership;
use Illuminate\Support\Facades\File;

class SendTemperaturLogToMothership
{
    public function execute()
    {
        $logfile = storage_path('logs/temperature.log');

        $mothership = Mothership::make(endpoint: config('services.mothership.production.endpoint'));

        if(File::exists($logfile) && File::size($logfile) >= config('taggy-recorder.temperature-log-min-size')) {
            $status = $mothership->sendTemperatureLog(File::get($logfile));
            if($status == 'OK') {
                File::delete($logfile);
            }
        }
    }
}
