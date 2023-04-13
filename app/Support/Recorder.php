<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\Crypto\Rsa\KeyPair;

class Recorder
{
    public const SYSTEM_ID_FILENAME = 'system-id.txt';

    public static function make()
    {
        return new self;
    }

    public function getSystemId()
    {
        return Storage::get(self::SYSTEM_ID_FILENAME);
    }

    public function getPublicKey()
    {
        return $this->getKey('public');
    }

    public function getPrivateKey()
    {
        return $this->getKey('private');
    }

    private function getKey($type)
    {
        $this->makeSureKeysExists();
        return File::get($this->getKeysPath() . '/' . $type. '.key');
    }

    private function generateKeys()
    {
        $keysPath = $this->getKeysPath();
        (new KeyPair())->generate($keysPath . '/private.key', $keysPath . '/public.key');
    }

    private function makeSureKeysExists()
    {
        if(!File::exists($this->getKeysPath() . '/public.key') || !File::exists($this->getKeysPath() . '/private.key')) {
            $this->generateKeys();
        }
    }

    private function getKeysPath()
    {
        $keysPath = storage_path('app/keys');
        if(!File::exists($keysPath)) {
            File::makeDirectory($keysPath);
        }
        return $keysPath;
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
}
