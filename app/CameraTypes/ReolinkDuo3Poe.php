<?php

namespace App\CameraTypes;

use App\Enums\VideoFormat;

class ReolinkDuo3Poe extends Reolink
{
    public CONST VIDEO_WIDTH = 7680;
    public CONST VIDEO_HEIGHT = 2160;

    // used to identify the camera type from the API DevInfo response
    protected const MODEL_NAME = 'Reolink Duo 3 PoE';

    protected const VIDEO_FORMAT = VideoFormat::HlsHevc;

    public static function getRotation()
    {
        return 1 / (60 * static::VIDEO_WIDTH * 2);
    }
}
