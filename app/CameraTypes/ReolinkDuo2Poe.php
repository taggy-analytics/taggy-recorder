<?php

namespace App\CameraTypes;

use App\Enums\CameraStatus;
use App\Models\Camera;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class ReolinkDuo2Poe extends RtspCamera
{
    public static function discover()
    {
        return self::discoverByVendorMac('ec:71:db');
    }

    public function getName()
    {
        return __('Reolink Duo 2 PoE');
    }

    public static function getDefaultCredentials() : array
    {
        return [
            'user' => 'admin',
            'password' => ''
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

    protected function getRtspUrl(Camera $camera)
    {
        $password = $camera->credentials['password'];
        $passwordPart = strlen($password) > 0 ? ':' . $password : '';

        // Todo: use Reolink::network()->getRtspUrl()
        return "rtsp://{$camera->credentials['user']}{$passwordPart}@{$camera->ip_address}:554/h265Preview_01_main";
    }

    public function getStatus(Camera $camera)
    {
        $this->setReolinkApiConfig($camera);

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

        return match(true) {
            Str::contains($output, '401 Unauthorized') => CameraStatus::AUTHENTICATION_FAILED,
            Str::contains($output, '404 Stream Not Found') => CameraStatus::STREAM_NOT_FOUND,
            Str::contains($output, 'Connection refused') => CameraStatus::CONNECTION_REFUSED,
            Str::contains($output, 'Stream #0:0:') => CameraStatus::READY,
            default => CameraStatus::UNKNOWN_ERROR,
        };
    }

    private function setReolinkApiConfig(Camera $camera)
    {
        config(['services.reolink.endpoint' => 'https://' . $camera->ip_address]);
        config(['services.reolink.username' => $camera->credentials['user']]);
        config(['services.reolink.password' => $camera->credentials['password']]);
        config(['services.reolink.token-cache-key' => 'reolink-token-' . $camera->id]);
    }


}
