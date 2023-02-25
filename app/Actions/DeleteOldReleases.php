<?php

namespace App\Actions;

use App\Support\ReleaseManager;
use Illuminate\Support\Facades\File;

class DeleteOldReleases
{
    public function execute()
    {
        $releasesToKeep = ReleaseManager::releasesToKeep();

        foreach(ReleaseManager::getReleases() as $release) {
            if(!in_array($release, $releasesToKeep)) {
                File::deleteDirectory($release);
            }
        }
    }
}
