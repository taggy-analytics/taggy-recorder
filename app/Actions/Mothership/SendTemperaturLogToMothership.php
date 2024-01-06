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
            $data = collect(explode(PHP_EOL, trim(File::get($logfile))))
                ->mapWithKeys(function($line) {
                    $parts = explode(' ', $line);
                    return [$parts[0] . ' ' . $parts[1] => $parts[2]];
                });
            $status = $mothership->sendTemperatureLog($data);
            if($status == 'OK') {
                File::delete($logfile);
            }
        }
    }
}
