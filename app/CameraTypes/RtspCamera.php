<?php

namespace App\CameraTypes;

use App\Models\Camera;
use App\Models\Recording;
use App\Support\FFMpegCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

abstract class RtspCamera extends CameraType
{
    public function isAvailable(Camera $camera)
    {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $this->getRtspUrl($camera));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        $curlResult = curl_exec($ch);

        curl_close($ch);

        return $curlResult !== false;
    }

    public function startRecording(Camera $camera, Recording $recording)
    {
        $outputDirectory = Storage::disk('public')->path($recording->getPath('video'));
        $outputFile = $outputDirectory . '/video.m3u8';
        File::makeDirectory($outputDirectory, recursive: true);
        // $processId = FFMpegCommand::run($this->getRtspUrl($camera), $outputFile, '-tag:v hvc1 -f hls -hls_time ' . config('taggy-recorder.video-conversion.segment-duration') . ' -hls_playlist_type event -hls_segment_filename ' . $outputDirectory . '/video-%05d.m4s -c copy');
        $processId = FFMpegCommand::run($this->getRtspUrl($camera), $outputFile, '-tag:v hvc1 -f hls -hls_time ' . config('taggy-recorder.video-conversion.segment-duration') . ' -hls_list_size 0 -hls_segment_filename ' . $outputDirectory . '/video-%05d.m4s -c copy');

        $camera->update(['process_id' => $processId]);
    }

    public function stopRecording(Camera $camera)
    {
        // SIGKILL = 9; Constants do not always work for whatever reason
        return posix_kill($camera->process_id, 9);
    }

    public function getRtspUrl(Camera $camera)
    {
        return "rtsp://{$camera->credentials['user']}:{$camera->credentials['password']}@{$camera->ip_address}:554/h265Preview_01_main";
    }

    public function isRecording(Camera $camera)
    {
        if($camera->process_id && !file_exists( "/proc/{$camera->process_id}")) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
            info('I just killed the recording with process ID ' . $camera->process_id . ':', ['backtrace' => $backtrace]);
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
