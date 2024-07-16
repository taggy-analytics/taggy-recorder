<?php

namespace App\Actions;

use App\CameraTypes\CameraType;

class DiscoverNewCameras
{
    public function execute()
    {
        CameraType::discoverCameras();
    }
}
