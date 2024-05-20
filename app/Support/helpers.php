<?php

if (! function_exists('reportToMothership')) {
    function reportToMothership(\App\Enums\LogMessageType $type, $message = '', $data = [])
    {
        \App\Support\Recorder::make()->log($type, $message, $data);
    }
}
