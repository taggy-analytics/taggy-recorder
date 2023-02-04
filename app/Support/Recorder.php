<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Spatie\Crypto\Rsa\KeyPair;

class Recorder
{
    public static function make()
    {
        return new self;
    }

    public function getMachineId()
    {
        if(File::exists('/etc/machine-id')) {
            return trim(File::get('/etc/machine-id'));
        }
        return '12345';
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
}
