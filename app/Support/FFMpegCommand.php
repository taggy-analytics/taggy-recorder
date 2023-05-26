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
        return $process->id() + 1;
    }
}
