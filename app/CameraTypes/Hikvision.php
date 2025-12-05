<?php

namespace App\CameraTypes;

use App\Enums\CameraStatus;
use App\Enums\Codec;
use App\Enums\StreamingProtocol;
use App\Enums\StreamQuality;
use App\Models\Camera;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

abstract class Hikvision extends RtspCamera
{
    protected $latency = 1;

    public $streamingProtocol = StreamingProtocol::HLS;

    public $codec = Codec::HEVC;

    public static function discover()
    {
        return self::discoverByVendorMac('0c:75:d2')
            ->filter(function ($camera) {
                try {
                    $credentials = self::getDefaultCredentials();

                    return \App\Services\Hikvision::make('https://'.$camera['ipAddress'] . '/ISAPI', $credentials['user'], $credentials['password'])->getDeviceInfo() == static::MODEL_NAME;
                } catch (ConnectionException $e) {
                    return false;
                }
            });
    }

    public function getName()
    {
        return static::MODEL_NAME;
    }

    public static function getDefaultCredentials(): array
    {
        return [
            'user' => 'admin',
            'password' => 'GaD_Pja-u6WfZU',
        ];
    }

    public function getFields()
    {
        return [
            'user' => [
                'name' => __('User'),
                'rules' => 'required',
                'description' => __('The username to login to the camera'),
            ],
            'password' => [
                'name' => __('Password'),
                'rules' => 'required',
                'description' => __('The password to login to the camera'),
            ],
        ];
    }

    public function getRtspUrl(Camera $camera, StreamQuality $quality = StreamQuality::PANORAMA)
    {
        $password = $camera->credentials['password'];
        $passwordPart = strlen($password) > 0 ? ':'.$password : '';

        $channel = match($quality) {
            StreamQuality::PANORAMA => 101,
            StreamQuality::BROADCAST => 201,
        };

        return "rtsp://{$camera->credentials['user']}{$passwordPart}@{$camera->ip_address}:554/Streaming/Channels/" . $channel;
    }

    public function getStatus(Camera $camera)
    {
        $output = '';
        $process = new Process(['ffprobe', '-hide_banner', $this->getRtspUrl($camera, StreamQuality::LOW)]);
        $process->setTimeout(5);

        try {
            $process->run(function ($type, $buffer) use (&$output) {
                return $output .= $buffer;
            });
        } catch (ProcessTimedOutException $e) {
            return CameraStatus::NOT_REACHABLE;
        }

        $status = match (true) {
            Str::contains($output, 'No route to host') => CameraStatus::OFFLINE,
            Str::contains($output, '401 Unauthorized') => CameraStatus::AUTHENTICATION_FAILED,
            Str::contains($output, '404 Stream Not Found') => CameraStatus::STREAM_NOT_FOUND,
            Str::contains($output, 'Connection refused') => CameraStatus::CONNECTION_REFUSED,
            Str::contains($output, 'Stream #0:0:') => CameraStatus::READY,
            Str::contains($output, 'Network is unreachable') => CameraStatus::NOT_REACHABLE,
            default => CameraStatus::UNKNOWN_ERROR,
        };

        if ($status == CameraStatus::UNKNOWN_ERROR) {
            info('Camera unknown error');
            info($output);
        }

        return $status;
    }
}
