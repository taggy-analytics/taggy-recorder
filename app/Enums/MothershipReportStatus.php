<?php

namespace App\Enums;

enum MothershipReportStatus
{
    case Initialized;
    case VideoNotAvailableAnymore;
    case Processed;
}
