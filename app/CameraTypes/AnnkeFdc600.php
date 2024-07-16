<?php

namespace App\CameraTypes;

use App\Enums\CameraStatus;
use App\Enums\Codec;
use App\Enums\StreamingProtocol;
use App\Models\Camera;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class AnnkeFdc600 extends RtspCamera
{
    public CONST VIDEO_WIDTH = 3632;
    public CONST VIDEO_HEIGHT = 1632;

    protected $latency = 2;

    public $streamingProtocol = StreamingProtocol::HLS;
    public $codec = Codec::H264;

    protected const MODEL_NAME = 'Annke FDC600';

    public static function discover()
    {
        // ToDo: reactivate when/if Annke is used
        return new Collection();

        return self::discoverByVendorMac('80:be:af');
    }

    public function getName()
    {
        return static::MODEL_NAME;
    }

    public static function getDefaultCredentials() : array
    {
        return [
            'user' => 'admin',
            'password' => 'qwer1234',
        ];
    }


    public function getRtspUrl(Camera $camera)
    {
        $password = $camera->credentials['password'];
        $passwordPart = strlen($password) > 0 ? ':' . $password : '';

        return "rtsp://{$camera->credentials['user']}{$passwordPart}@{$camera->ip_address}:554/Streaming/Channels/101";
    }

    public function getStatus(Camera $camera)
    {
        $output = '';
        $process = new Process(['ffprobe', '-hide_banner', $this->getRtspUrl($camera)]);
        $process->setTimeout(5);

        try {
            $process->run(function ($type, $buffer) use (&$output) {
                return $output .= $buffer;
            });
        }
        catch(ProcessTimedOutException $e) {
            return CameraStatus::NOT_REACHABLE;
        }

        $status = match(true) {
            Str::contains($output, 'No route to host') => CameraStatus::OFFLINE,
            Str::contains($output, '401 Unauthorized') => CameraStatus::AUTHENTICATION_FAILED,
            Str::contains($output, '404 Stream Not Found') => CameraStatus::STREAM_NOT_FOUND,
            Str::contains($output, 'Connection refused') => CameraStatus::CONNECTION_REFUSED,
            Str::contains($output, 'Stream #0:0:') => CameraStatus::READY,
            Str::contains($output, 'Network is unreachable') => CameraStatus::NOT_REACHABLE,
            default => CameraStatus::UNKNOWN_ERROR,
        };

        if($status == CameraStatus::UNKNOWN_ERROR) {
            info('Camera unknown error');
            info($output);
        }

        return $status;
    }
}
