<?php

namespace App\CameraTypes;

use App\Models\Camera;
use App\Support\FFMpegCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

abstract class RtspCamera extends CameraType
{
    public function startRecording(Camera $camera)
    {
        $recording = $camera->recordings()->create([
            'name' => now()->toDateTimeLocalString(),
        ]);

        info('Starting recording # ' . $recording->id . ' for camera #' . $camera->id . ': ' . $this->getRtspUrl($camera));

        $outputDirectory = Storage::disk('public')->path($recording->getPath('video'));
        $outputFile = $outputDirectory . '/video.m3u8';
        File::makeDirectory($outputDirectory, recursive: true);
        $processId = FFMpegCommand::run($this->getRtspUrl($camera), $outputFile, '-tag:v hvc1 -f hls -hls_time ' . config('taggy-recorder.video-conversion.segment-duration') . ' -hls_playlist_type event -hls_segment_filename ' . $outputDirectory . '/video-%05d.m4s -c copy');

        $camera->update(['process_id' => $processId]);
    }

    public function stopRecording(Camera $camera)
    {
        // posix_kill($camera->process_id, SIGINT);
        return posix_kill($camera->process_id, 2);
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
