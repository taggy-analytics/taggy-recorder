<?php

namespace App\Actions;

use App\Support\Recorder;
use Illuminate\Support\Facades\Process;

class MeasureTemperature
{
    public function execute()
    {
        $pattern = '/temp=([0-9]+(\.[0-9]+)?)\'C/';

        if (preg_match($pattern, Process::run('vcgencmd measure_temp')->output(), $matches)) {
            $temperature = $matches[1];
            if ($temperature > config('taggy-recorder.temperature-log-min')) {
                Recorder::make()->logMeasure('temperature', $temperature);
            }
        }
    }
}
