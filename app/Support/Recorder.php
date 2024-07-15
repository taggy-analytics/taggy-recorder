<?php

namespace App\Support;

use App\Actions\CalculateLed;
use App\Actions\CheckIfAllNeededServicesAreUpAndRunning;
use App\Enums\LedColor;
use App\Enums\LogMessageType;
use App\Models\LivestreamSegment;
use App\Models\RecorderLog;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;
use Spatie\Crypto\Rsa\KeyPair;

class Recorder
{
    public const CURRENT_SOFTWARE_VERSION_FILENAME = 'software-version.txt';
    public const INSTALLATION_FINISHED_FILENAME = 'installation-finished.txt';
    public const RUNNING_UPLOAD_FILENAME = 'running-upload.txt';
    public const CURRENT_LEDS_FILENAME = 'current-leds.txt';

    public static function make()
    {
        return new self;
    }

    public function getSystemId()
    {
        if(!DotenvEditor::keyExists('SYSTEM_ID')) {
            DotenvEditor::setKey('SYSTEM_ID', Str::random(16));
            DotenvEditor::save();
        }

        return DotenvEditor::getValue('SYSTEM_ID');
    }

    public function getRunningFfmpegProcesses()
    {
        return $this->getRunningProcesses('ffmpeg');
    }

    private function getRunningProcesses($string)
    {
        exec('ps ahxwwo pid:1,command:1 |grep "' . $string .'"', $processes);

        return collect($processes)
            ->map(fn($process) => preg_split('/\s+/', trim($process)))
            ->map(fn($process) => [
                'processId' => Arr::get($process, 0),
                'input' => Arr::get($process, 3),
            ])
            ->filter(fn($process) => !in_array($process['input'], [null, 'ps']));
    }

    public function led($colors, $interval = 0)
    {
        $colors = collect($colors)->pluck('value')->implode('/');

        $status = [$colors, $interval];

        if($this->currentLeds() != $status || count($this->getRunningProcesses('led.py')) == 1) {
            foreach($this->getRunningProcesses('led.py') as $process) {
                posix_kill($process['processId'], 9);
            }

            $command = "python3 led.py {$colors} {$interval} > /dev/null 2>&1 &";
            shell_exec($command);

            $this->currentLeds($status);
        }
    }

    public function installationIsFinished()
    {
        return Storage::exists(self::INSTALLATION_FINISHED_FILENAME);
    }

    public function allNeededServicesAreUpAndRunning()
    {
        return app(CheckIfAllNeededServicesAreUpAndRunning::class)->execute();
    }

    public function waitUntilAllNeededServicesAreUpAndRunning()
    {
        while(!Recorder::make()->allNeededServicesAreUpAndRunning()) {
            sleep(5);
        };
    }

    private function currentLeds($leds = null)
    {
        if(is_null($leds)) {
            return json_decode(Storage::get(self::CURRENT_LEDS_FILENAME), true);
        }

        Storage::put(self::CURRENT_LEDS_FILENAME, json_encode($leds));
    }

    public function isUpdatingFirmware()
    {
        return $this->getRunningProcesses('taggy:update-software')->count() > 1;
    }

    public function isLivestreaming()
    {
        return LivestreamSegment::latest('uploaded_at')
            ->first()
            ->uploaded_at
            ->gt(now()->subSeconds(10));
    }

    public function isUploading($uploading = null)
    {
        if(!Mothership::make()->isOnline()) {
            Storage::delete(self::RUNNING_UPLOAD_FILENAME);
        }

        if(is_null($uploading)) {
            return Storage::exists(self::RUNNING_UPLOAD_FILENAME);
        }
        elseif($uploading) {
            Storage::put(self::RUNNING_UPLOAD_FILENAME, '');
        }
        else {
            Storage::delete(self::RUNNING_UPLOAD_FILENAME);
        }

        app(CalculateLed::class)->execute();
    }

    public function logMeasure($type, $message)
    {
        File::append(storage_path('logs/' . $type . '.log'), now()->toDateTimeString() . ' ' . $message . PHP_EOL);
    }

    public function getUptime()
    {
        $uptime = explode(' ', file_get_contents('/proc/uptime'));
        $uptime_seconds = floatval($uptime[0]);
        return $uptime_seconds;
    }

    public function getPublicKey()
    {
        $keysDirectory = storage_path('app/keys');

        if(!File::exists($keysDirectory . '/public.key')) {
            (new KeyPair())->generate($keysDirectory . '/private.key', $keysDirectory . '/public.key');
        }

        return File::get($keysDirectory . '/public.key');
    }

    public function log(LogMessageType $type, $message = '', $data = [])
    {
        RecorderLog::firstOrCreate(compact('type', 'message'), compact('data'));
    }


    public function currentSoftwareVersion()
    {
        return Storage::get(self::CURRENT_SOFTWARE_VERSION_FILENAME);
    }
}
