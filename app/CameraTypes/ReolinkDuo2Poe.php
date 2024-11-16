<?php

namespace App\CameraTypes;

use App\Enums\VideoFormat;

class ReolinkDuo2Poe extends Reolink
{
    public CONST VIDEO_WIDTH = 4608;
    public CONST VIDEO_HEIGHT = 1728;

    // used to identify the camera type from the API DevInfo response
    protected const MODEL_NAME = 'Reolink Duo 2 PoE';

    protected const VIDEO_FORMAT = VideoFormat::HlsHevc;
}
