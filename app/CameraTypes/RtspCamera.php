<?php

namespace App\CameraTypes;

use App\Enums\CameraStatus;
use App\Enums\StreamQuality;
use App\Models\Camera;
use App\Models\Recording;
use App\Support\FFMpegCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

abstract class RtspCamera extends CameraType
{
    abstract public function getRtspUrl(Camera $camera, StreamQuality $quality = StreamQuality::HIGH);

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

    /*
    public function startRecording(Camera $camera, Recording $recording)
    {
        $outputDirectory = Storage::disk('public')->path($recording->getPath('video'));
        $outputFile = $outputDirectory . '/video.m3u8';
        File::makeDirectory($outputDirectory, recursive: true);
        // Nächster Versuch laut ChatGPT: Audio Reencoding (zu AAC)
        FFMpegCommand::run($this->getRtspUrl($camera), $outputFile, '-tag:v hvc1 -f hls -hls_time ' . config('taggy-recorder.video-conversion.segment-duration') . ' -hls_list_size 0 -hls_segment_filename ' . $outputDirectory . '/video-%05d.ts -c copy', '-use_wallclock_as_timestamps 1 -fflags +genpts');
        // FFMpegCommand::run($this->getRtspUrl($camera), $outputFile, '-tag:v hvc1 -f hls -hls_time ' . config('taggy-recorder.video-conversion.segment-duration') . ' -hls_list_size 0 -hls_segment_filename ' . $outputDirectory . '/video-%05d.m4s -c copy');
    }
    */

    public function startRecording(Camera $camera, Recording $recording)
    {
        $outputDirectory = Storage::disk('public')->path($recording->getPath('video'));
        $outputFile = $outputDirectory . '/video.m3u8';
        $segmentDuration = config('taggy-recorder.video-conversion.segment-duration');
        $segmentFilename = $outputDirectory . '/video-%05d.ts';

        $beforeInputOptions = [
            '-use_wallclock_as_timestamps 1',
            '-fflags +genpts',
            '-rtsp_transport tcp',
            config('taggy-recorder.ffmpeg.logging') ? '-loglevel debug' : '',
        ];

        $options = [
            '-tag:v hvc1',
            '-f hls -hls_time ' . $segmentDuration,
            '-hls_list_size 0',
            'hls_segment_filename ' . $segmentFilename,
            '-c:v copy',
            '-c:a aac',
            '-b:a 96k',
            '-avoid_negative_ts make_zero',
        ];

        FFMpegCommand::run($this->getRtspUrl($camera), $outputFile, $options, $beforeInputOptions);
    }

    public function stopRecording(Camera $camera)
    {
        shell_exec("pkill -f 'ffmpeg.*" . $camera->recordings()->latest()->first()->key . "'");

        return true;
    }

    public function isRecording(Camera $camera)
    {
        if($camera->status != CameraStatus::READY) {
            return false;
        }

        $key = $camera->recordings()->latest()->first()?->key;

        if(empty($key)) {
            return false;
        }

        return str_contains(shell_exec("pgrep -fl " . $key), 'ffmpeg');
    }
}
