<?php

namespace App\CameraTypes;

use App\Enums\VideoFormat;

class ReolinkE1OutdoorPoe extends Reolink
{
    public CONST VIDEO_WIDTH = 3840;
    public CONST VIDEO_HEIGHT = 2160;

    // used to identify the camera type from the API DevInfo response
    protected const MODEL_NAME = 'E1 Outdoor PoE';

    protected const VIDEO_FORMAT = VideoFormat::HlsHevc;

    public static function getRotation()
    {
        return 1 / (60 * static::VIDEO_WIDTH * 2);
    }
}
