<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class ReleaseManager
{
    public static function getReleases()
    {
        $directories = File::directories(base_path('..'));
        sort($directories);

        return $directories;
    }

    public static function releasesToKeep()
    {
        return array_slice(self::getReleases(), -config('taggy-recorder.releases-to-keep'));
    }

    public static function currentRelease()
    {
        return trim(shell_exec('readlink '.escapeshellarg(base_path('../../current'))));
    }
}
