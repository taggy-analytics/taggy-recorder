<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class Uploader
{
    public static function make()
    {
        return new self;
    }

    public function register($file)
    {
        Cache::lock('fifo-lock', 10)->block(10, function () use ($file) {
            $filesStack = cache()->get('files-to-upload-stack');
            $filesStack[] = $file;
            cache()->forever('files-to-upload-stack', $filesStack);
        });
    }

    public function uploadNext()
    {
        Mothership::make()
            ->
    }
}
