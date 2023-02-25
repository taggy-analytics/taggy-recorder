<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Arr;

class ReleaseManager
{
    public static function getReleases()
    {
        return File::directories(base_path('..'));
    }

    public static function releasesToKeep()
    {
        return array_slice(self::getReleases(), -config('taggy-recorder.releases-to-keep'));
    }

    public static function currentRelease()
    {
        return Arr::last(self::getReleases());
    }
}
