<?php

namespace App\CameraTypes;

use App\Enums\VideoFormat;

class HikvisionSportsPoe extends Hikvision
{
    public const VIDEO_WIDTH = 4608;

    public const VIDEO_HEIGHT = 1728;

    // used to identify the camera type from the API DevInfo response
    protected const MODEL_NAME = 'DS-2CD6982G0-U';

    protected const VIDEO_FORMAT = VideoFormat::HlsHevc;
}
