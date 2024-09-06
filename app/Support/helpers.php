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
        $memoryConsumers = blink()->get('memoryConsumers', []);

        $memoryUsage = memory_get_usage();

        if(blink()->has('lastMemoryUsage')) {
            $memoryAdded = $memoryUsage - blink()->get('lastMemoryUsage');
            if(Arr::has($memoryConsumers, $key)) {
                $memoryConsumers[$key] += $memoryAdded;
            }
            else {
                $memoryConsumers[$key] = $memoryAdded;
            }
        }

        blink()->put('lastMemoryUsage', $memoryUsage);
        blink()->put('memoryConsumers', $memoryConsumers);

        info($memoryConsumers);
    }
}
