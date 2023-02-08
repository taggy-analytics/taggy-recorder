<?php

namespace App\CameraTypes;

use App\Models\Camera;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

abstract class RtspCamera extends CameraType
{
    public function startRecording(Camera $camera)
    {
        info('Starting recording for camera #' . $camera->id . ': ' . $this->getRtspUrl($camera));

        $outputDirectory = $camera->storagePath() . '/' . str_replace(':', '-', now()->toDateTimeLocalString()) . '/video';
        $outputFile = $outputDirectory . '/playlist.m3u8';
        File::makeDirectory($outputDirectory);
        $this->runFFmpegCommand($this->getRtspUrl($camera), $outputFile, '-codec copy -start_number 0 -hls_time ' . config('taggy-recorder.video-conversion.segment-duration') . ' -hls_list_size 0 -f hls');
    }

    public function stopRecording(Camera $camera)
    {
        exec('kill -9 ' . Arr::get($this->getProcess($camera), 'processId'));
    }

    private function getRtspUrl(Camera $camera)
    {
        return "rtsp://{$camera->credentials['user']}:{$camera->credentials['password']}@{$camera->ip_address}:554/h265Preview_01_main";
    }

    public function isRecording(Camera $camera)
    {
        return filled($this->getProcess($camera));
    }

    private function getProcess(Camera $camera)
    {
        return $this->getRunningFfmpegProcesses()
            ->filter(fn($process) => $process['input'] == $this->getRtspUrl($camera))
            ->first();
    }
}
