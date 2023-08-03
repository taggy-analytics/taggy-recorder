<?php

namespace App\Support;

use App\Enums\LogMessageType;
use App\Models\RecorderLog;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;

class Recorder
{
    public const INSTALLATION_FINISHED_FILENAME = 'installation-finished.txt';

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
        exec('ps ahxwwo pid:1,command:1 |grep "ffmpeg"', $processes);

        return collect($processes)
            ->map(fn($process) => preg_split('/\s+/', trim($process)))
            ->map(fn($process) => [
                'processId' => Arr::get($process, 0),
                'input' => Arr::get($process, 3),
            ])
            ->filter(fn($process) => !in_array($process['input'], [null, 'ps']));
    }



    public function installationIsFinished()
    {
        return Storage::exists(self::INSTALLATION_FINISHED_FILENAME);
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
