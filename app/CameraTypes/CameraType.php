<?php

namespace App\CameraTypes;

use App\Enums\CameraStatus;
use App\Models\Camera;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

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
            $process = Process::run([config('services.cli.arp'), '-a']);

            return collect(explode(PHP_EOL, $process->output()))
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
}
