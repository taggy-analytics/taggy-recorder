<?php

namespace App\CameraTypes;

use App\Models\Camera;
use App\Support\Recorder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

abstract class RtspCamera extends CameraType
{
    public function startRecording(Camera $camera)
    {
        $recording = $camera->recordings()->create([
            'name' => now()->toDateTimeLocalString(),
        ]);

        info('Starting recording # ' . $recording->id . ' for camera #' . $camera->id . ': ' . $this->getRtspUrl($camera));

        $outputDirectory = $camera->storagePath() . '/' . $recording->id . '/video';
        $outputFile = $outputDirectory . '/video-%05d.mp4';
        File::makeDirectory($outputDirectory, recursive: true);
        $processId = $this->runFFmpegCommand($this->getRtspUrl($camera), $outputFile, '-c:v copy -c:a copy -f segment -segment_list ' . $outputDirectory . '.m3u8 -segment_list_type m3u8 -segment_time ' . config('taggy-recorder.video-conversion.segment-duration'));
        $camera->update(['process_id' => $processId + 1]);  // I don't know why, but the returned process ID is one less than the actual process ID
    }

    public function stopRecording(Camera $camera)
    {
        posix_kill($camera->process_id, SIGINT);
    }

    private function getRtspUrl(Camera $camera)
    {
        return "rtsp://{$camera->credentials['user']}:{$camera->credentials['password']}@{$camera->ip_address}:554/h265Preview_01_main";
    }

    public function isRecording(Camera $camera)
    {
        if($camera->process_id && !file_exists( "/proc/{$camera->process_id}")) {
            $camera->update(['process_id' => null]);
        }

        return filled($camera->process_id);
    }

    /*
    private function getProcess(Camera $camera)
    {
        return Recorder::make()->getRunningFfmpegProcesses()
            ->filter(fn($process) => $process['input'] == $this->getRtspUrl($camera))
            ->first();
    }
    */
}
