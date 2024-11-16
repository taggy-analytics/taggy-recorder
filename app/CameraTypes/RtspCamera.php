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
        return $camera->getStatus(false) == CameraStatus::READY;

        /*
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $this->getRtspUrl($camera));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        $curlResult = curl_exec($ch);

        curl_close($ch);

        return $curlResult !== false;
        */
    }


    public function startRecording(Camera $camera, Recording $recording)
    {
        $outputDirectory = Storage::disk('public')->path($recording->getPath('video'));
        $outputFile = $outputDirectory . '/video.m3u8';
        $segmentDuration = config('taggy-recorder.video-conversion.segment-duration');
        $segmentFilename = $outputDirectory . '/video-%05d.ts';

        $beforeInputOptions = [
            '-use_wallclock_as_timestamps 1',
            '-fflags +genpts',
            // '-rtsp_transport tcp',  // Don't add this ever! It will crash the recording
            config('taggy-recorder.ffmpeg.logging') ? '-loglevel ' . config('taggy-recorder.ffmpeg.logging-level') : '',
        ];

        $options = [
            '-tag:v hvc1',
            '-f hls',
            '-hls_time ' . $segmentDuration,
            '-hls_list_size 0',
            '-hls_segment_filename ' . $segmentFilename,
            '-c:v copy',
            '-avoid_negative_ts make_zero',
        ];

        if(config('taggy-recorder.ffmpeg.record-audio')) {
            $options[] = '-c:a aac';
            $options[] = '-b:a 96k';
        }
        else {
            $options[] = '-an';
        }

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

        $key = $camera->getLatestRecording()?->key;

        if(empty($key)) {
            return false;
        }

        return str_contains(shell_exec("pgrep -fl " . $key), 'ffmpeg');
    }
}
