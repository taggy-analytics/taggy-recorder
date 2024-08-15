<?php

namespace App\Support;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class FFMpegCommand
{
    public static function run($inputFile, $outputFile, $command, $beforeInputOptions = '')
    {
        if(is_array($command)) {
            $command = implode(' ', $command);
        }
        if(is_array($beforeInputOptions)) {
            $beforeInputOptions = implode(' ', $beforeInputOptions);
        }

        $command = "$beforeInputOptions -i $inputFile $command $outputFile";

        if(config('taggy-recorder.ffmpeg.logging')) {
            $logFile = Str::replaceLast(basename($outputFile), 'ffmpeg.log', $outputFile);
            $command .= " > $logFile 2>&1";
        }

        return self::runRaw($command);
    }

    public static function runRaw($command, $app = 'ffmpeg', $async = true)
    {
        $command = $app . ' ' . trim($command);
        info($command);
        $process = $async ? Process::start($command) : Process::run($command);

        // sh -c is called, which starts the actual ffmpeg process
        return $async ? ($process->id() + 1) : Process::run($command)->output();
    }

    public static function convertSeconds($originalSeconds)
    {
        $hours = floor(floor($originalSeconds) / 3600);
        $minutes = floor(floor(($originalSeconds / 60)) % 60);
        $seconds = floor($originalSeconds) % 60;
        $milliseconds = floor(($originalSeconds - floor($originalSeconds)) * 1000);

        return sprintf('%02d:%02d:%02d.%03d', $hours, $minutes, $seconds, $milliseconds);
    }
}
