<?php

if (! function_exists('reportToMothership')) {
    function reportToMothership(\App\Enums\LogMessageType $type, $message = '', $data = [])
    {
        \App\Support\Recorder::make()->log($type, $message, $data);
    }
}

if (! function_exists('checkMemory')) {
    function checkMemory($key = null)
    {
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
                $memoryConsumers[$key] = $memoryUsage;
            }
        }

        cache()->put('lastMemoryUsage', $memoryUsage);
        cache()->put('memoryConsumers', $memoryConsumers);

        info($memoryConsumers);
    }
}
