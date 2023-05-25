<?php

namespace App\Support;

use Illuminate\Support\Facades\Process;

class FFMpegCommand
{
    public static function run($inputFile, $outputFile, $command)
    {
        $command = "ffmpeg -i $inputFile $command $outputFile";
        info($command);

        $process = Process::start($command);

        info('Process ID: ' . $process->id());

        // sh -c is called, which starts the actual ffmpeg process
        return $process->id() + 1;
    }
}
