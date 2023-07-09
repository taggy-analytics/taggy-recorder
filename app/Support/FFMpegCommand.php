<?php

namespace App\Support;

use Illuminate\Support\Facades\Process;

class FFMpegCommand
{
    public static function run($inputFile, $outputFile, $command)
    {
        $command = "-i $inputFile $command $outputFile";
        return self::runRaw($command);
    }

    public static function runRaw($command, $app = 'ffmpeg', $async = true)
    {
        $command = $app . ' ' . $command;
        info($command);
        $process = $async ? Process::start($command) : Process::run($command);

        // sh -c is called, which starts the actual ffmpeg process
        return $async ? ($process->id() + 1) : Process::run($command)->output();
    }

    public static function convertSeconds($originalSeconds)
    {
        $hours = floor($originalSeconds / 3600);
        $minutes = floor(($originalSeconds / 60) % 60);
        $seconds = $originalSeconds % 60;
        $milliseconds = floor(($originalSeconds - floor($originalSeconds)) * 1000);

        return sprintf('%02d:%02d:%02d.%03d', $hours, $minutes, $seconds, $milliseconds);
    }
}
