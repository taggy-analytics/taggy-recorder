<?php

namespace App\CameraTypes;

class ReolinkDuo3Poe extends Reolink
{
    public CONST VIDEO_WIDTH = 7680;
    public CONST VIDEO_HEIGHT = 2170;

    // used to identify the camera type from the API DevInfo response
    protected const MODEL_NAME = 'Reolink Duo 2 PoE';
}
