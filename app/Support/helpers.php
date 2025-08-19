<?php

use App\Exceptions\NotInProMode;
use App\Support\Recorder;

if (! function_exists('reportToMothership')) {
    function reportToMothership(\App\Enums\LogMessageType $type, $message = '', $data = [])
    {
        \App\Support\Recorder::make()->log($type, $message, $data);
    }
}

if (! function_exists('checkMemory')) {
    function checkMemory($key = null, $initialize = false)
    {
        if($initialize) {
            cache()->forget('memoryConsumers');
            cache()->forget('lastMemoryUsage');
        }

        $backtrace = debug_backtrace()[0];
        $key ??= basename($backtrace['file']) . '-' . $backtrace['line'];
        $memoryConsumers = cache()->get('memoryConsumers', []);

        $memoryUsage = memory_get_usage();

        if(cache()->has('lastMemoryUsage')) {
            $memoryAdded = $memoryUsage - cache()->get('lastMemoryUsage');
            if(Arr::has($memoryConsumers, $key)) {
                $memoryConsumers[$key] += $memoryAdded;
            }
            else {
                $memoryConsumers[$key] = $memoryAdded;
            }
        }

        cache()->put('lastMemoryUsage', $memoryUsage);
        cache()->put('memoryConsumers', $memoryConsumers);

        info($memoryConsumers);
    }
}

if (! function_exists('preventMemoryLeak')) {
    function preventMemoryLeak($message = null, $limit = 300000000)
    {
        if(memory_get_usage() > $limit) {
            info(($message ?? 'Memory leak prevented') .  ' (Memory: ' . memory_get_usage() .')');
            exit;
        }
    }
}

if (! function_exists('requireProMode')) {
    function requireProMode($message = null)
    {
        if(!Recorder::make()->inProMode()) {
            $message ??= 'Pro mode needed for this functionality.';
            throw new NotInProMode($message);
        }
    }
}
