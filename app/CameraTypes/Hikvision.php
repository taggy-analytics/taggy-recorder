<?php

namespace App\CameraTypes;

use App\Enums\CameraStatus;
use App\Enums\Codec;
use App\Enums\StreamingProtocol;
use App\Enums\StreamQuality;
use App\Models\Camera;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
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
        $hikvisionCameras = self::discoverByVendorMac('0c:75:d2')
            ->filter(function ($camera) {
                try {
                    return self::getApiClient($camera)->getDeviceInfo()['model'] == static::MODEL_NAME;
                } catch (ConnectionException $e) {
                    return false;
                }
            });

        $cameras = new Collection;

        // For now we create two "virtual" cameras - one with PANORAMA and one with BROADCAST
        foreach ($hikvisionCameras as $camera) {
            $name = self::getApiClient($camera)->getDeviceInfo()['name'];

            $cameras[] = [
                'name' => $name . ' Broadcast',
                'identifier' => $camera['identifier'] . '_Broadcast',
                'ipAddress' => $camera['ipAddress'],
                'rotation' => 0,
                'width' => 1920,
                'height' => 1080,
            ];

            $cameras[] = [
                'name' => $name . ' Panorama',
                'identifier' => $camera['identifier'] . '_Broadcast',
                'ipAddress' => $camera['ipAddress'],
                'rotation' => 0.00000108507,
                'width' => 7168,
                'height' => 2160,
            ];
        }

        return $cameras;
    }

    private static function getApiClient($camera, $credentials = null)
    {
        $credentials ??= self::getDefaultCredentials();

        return \App\Services\Hikvision::make(
            $camera['ipAddress'],
            $credentials['user'],
            $credentials['password'],
        );
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
        $passwordPart = strlen($password) > 0 ? ':' . $password : '';

        $channel = match ($quality) {
            StreamQuality::PANORAMA => 101,
            StreamQuality::BROADCAST => 201,
        };

        return "rtsp://{$camera->credentials['user']}{$passwordPart}@{$camera->ip_address}:554/Streaming/Channels/" . $channel;
    }

    public function getStatus(Camera $camera)
    {
        $output = '';
        $process = new Process(['ffprobe', '-hide_banner', $this->getRtspUrl($camera, StreamQuality::PANORAMA)]);
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

    protected function getRecordingStreamQuality(Camera $camera)
    {
        return Str::endsWith($camera->name, 'Panorama') ?
            StreamQuality::PANORAMA :
            StreamQuality::BROADCAST;
    }
}
