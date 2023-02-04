<?php

namespace App\CameraTypes;

use App\Enums\CameraStatus;
use App\Models\Camera;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

abstract class CameraType
{
    abstract public static function discover();
    abstract public function getName();
    abstract public function getFields();
    abstract public function getStatus(Camera $camera);

    abstract public static function getDefaultCredentials() : array;

    abstract public function startRecording(Camera $camera);
    abstract public function stopRecording(Camera $camera);
    abstract public function isRecording(Camera $camera);

    public static function discoverCameras()
    {
        $newCameras = collect();

        $cameraIdentifiers = Camera::pluck('identifier');

        foreach(self::getCameraClasses() as $cameraClass) {
            $newCamerasForClass = $cameraClass::discover()
                ->filter(fn($newCamera) => !$cameraIdentifiers->contains($newCamera['identifier']));

            foreach($newCamerasForClass as $newCamera) {
                $newCameras[] = Camera::create([
                    'identifier' => $newCamera['identifier'],
                    'type' => $cameraClass::class,
                    'name' => $newCamera['host'],
                    'status' => CameraStatus::DISCOVERED,
                    'ip_address' => $newCamera['ipAddress'],
                    'credentials' => $cameraClass::getDefaultCredentials(),
                ]);
            }
        }

        return $newCameras;
    }

    private static function getCameraClasses()
    {
        $cameras = [];
        foreach (File::files(__DIR__) as $file) {
            $className = __NAMESPACE__ . '\\' . $file->getFilenameWithoutExtension();
            if (!(new \ReflectionClass($className))->isAbstract()) {
                $cameras[] = new $className();
            }
        }
        return collect($cameras);
    }

    protected static function getDevices()
    {
        $dnsMasqLeasesFile = config('services.dnsmasq.leases.path');

        if(File::exists($dnsMasqLeasesFile)) {
            return collect(explode(PHP_EOL, File::get($dnsMasqLeasesFile)))
                ->map(fn($line) => explode(' ', $line))
                ->filter(fn($line) => count($line) == 5)
                ->map(fn($line) => [
                    'host' => $line[3],
                    'ipAddress' => str_replace(['(', ')'], '', $line[2]),
                    'macAddress' => $line[1],
                ]);
        }
        else {
            $process = new Process([config('services.cli.arp'), '-a']);
            $process->run();

            return collect(explode(PHP_EOL, $process->getOutput()))
                ->map(fn($line) => explode(' ', $line))
                ->filter(fn($line) => count($line) > 5)
                ->map(fn($line) => [
                    'host' => $line[0],
                    'ipAddress' => str_replace(['(', ')'], '', $line[1]),
                    'macAddress' => $line[3],
                ]);
        }
    }

    protected static function discoverByVendorMac($mac)
    {
        return self::getDevices()
            ->filter(fn($device) => Str::startsWith(strtolower($device['macAddress']), strtolower($mac)))
            ->filter(fn($device) => !Camera::pluck('ip_address')->contains($device['ipAddress']))
            ->map(function($device) {
                $device['identifier'] = $device['macAddress'];
                return $device;
            })
            ->values();
    }

    protected function getRunningFfmpegProcesses()
    {
        exec('ps ahxwwo pid:1,command:1 |grep "ffmpeg"', $processes);

        return collect($processes)
            ->map(fn($process) => preg_split('/\s+/', trim($process)))
            ->map(fn($process) => [
                'processId' => Arr::get($process, 0),
                'input' => Arr::get($process, 3),
            ])
            ->filter(fn($process) => !in_array($process['input'], [null, 'ps']));
    }
}
