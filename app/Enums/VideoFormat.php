<?php

namespace App\Enums;

enum VideoFormat : string
{
    case DashAv1MultiStream = 'DashAv1MultiStream';
    case DashAv1SingleStream = 'DashAv1SingleStream';
    case DashAv1SingleStreamShortSegments = 'DashAv1SingleStreamShortSegments';
    case HlsHevc = 'HlsHevc';
    case HlsH264 = 'HlsH264';
    case MkvAv1 = 'MkvAv1';
    case Mp4H264 = 'Mp4H264';
    case Mp4Hevc = 'Mp4Hevc';
    case Mp4Av1 = 'Mp4Av1';
}
