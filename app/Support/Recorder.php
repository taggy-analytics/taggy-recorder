<?php

namespace App\Support;

use App\Actions\CalculateLed;
use App\Enums\LedColor;
use App\Enums\LogMessageType;
use App\Models\RecorderLog;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;

class Recorder
{
    public const INSTALLATION_FINISHED_FILENAME = 'installation-finished.txt';
    public const RUNNING_UPLOAD_FILENAME = 'running-upload.txt';

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

    public function getRouterPassword()
    {
        if(DotenvEditor::keyExists('GLINET_PASSWORD')) {
            return false;
        }
        else {
            $password = Str::random();
            DotenvEditor::setKey('GLINET_PASSWORD', $password);
            return $password;
        }
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

    public function led(LedColor $color, $interval = null)
    {
        foreach($this->getRunningProcesses('taggy:led') as $process) {
            posix_kill($process['processId'], 9);
        }

        $command = "php artisan taggy:led {$color->value}" . ($interval ? " {$interval}" : "") . " > /dev/null 2>&1 &";
        shell_exec($command);
    }

    public function installationIsFinished()
    {
        return Storage::exists(self::INSTALLATION_FINISHED_FILENAME);
    }

    public function isUploading($uploading = null)
    {
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
        File::append(storage_path('logs/' . $type . '.log'), now()->toDateString() . ' ' . $message);
    }

    public function getUptime()
    {
        $uptime = explode(' ', file_get_contents('/proc/uptime'));
        $uptime_seconds = floatval($uptime[0]);
        return $uptime_seconds;
    }

    public function log(LogMessageType $type, $message = '', $data = [])
    {
        RecorderLog::firstOrCreate(compact('type', 'message'), compact('data'));
    }
}
