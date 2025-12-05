<?php

namespace App\Enums;

enum StreamQuality
{
    // Reolink
    case HIGH;
    case LOW;

    // Hikvision Sport Cams
    case PANORAMA;
    case BROADCAST;
}
