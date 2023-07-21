<?php

namespace App\CameraTypes;

use App\Data\CredentialsStatusData;
use App\Enums\CameraStatus;
use App\Models\Camera;
use App\Models\Recording;
use App\Services\GliNet;
use Illuminate\Support\Collection;
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

    abstract public function startRecording(Camera $camera, Recording $recording);
    abstract public function stopRecording(Camera $camera);
    abstract public function isRecording(Camera $camera);

    protected $recordingStartDelay = 0;

    public function getRecordingStartDelay()
    {
        return $this->recordingStartDelay;
    }

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
                    'name' => $newCamera['name'],
                    'status' => CameraStatus::DISCOVERED,
                    'ip_address' => $newCamera['ipAddress'],
                    'credentials' => $cameraClass::getDefaultCredentials(),
                    'credentials_status' => new CredentialsStatusData(),
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
        try {
            return GliNet::make()
                ->clients()
                ->map(fn($client) => [
                    'identifier' => $client['mac'],
                    'name' => $client['name'],
                    'ipAddress' => $client['ip'],
                ]);
        }
        catch(\Exception $exception) {
            return new Collection();
        }
    }

    protected static function discoverByVendorMac($mac)
    {

        return self::getDevices()
            ->filter(fn($device) => Str::startsWith(strtolower($device['identifier']), strtolower($mac)))
            ->filter(fn($device) => !Camera::pluck('ip_address')->contains($device['ipAddress']))
            ->values();
    }
}
