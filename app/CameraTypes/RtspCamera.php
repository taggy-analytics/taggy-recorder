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
        info('ffmpeg -i "' . $this->getRtspUrl($camera) . '" -codec copy "' . $camera->storagePath() . '/' . str_replace(':', '-', now()->toDateTimeLocalString()) . '.ts"');

        exec('nohup ffmpeg -i "' . $this->getRtspUrl($camera) . '" -codec copy "' . $camera->storagePath() . '/' . str_replace(':', '-', now()->toDateTimeLocalString()) . '.ts" 2> /dev/null > /dev/null &');
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
