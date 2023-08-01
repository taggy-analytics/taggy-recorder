<?php

namespace App\Enums;

enum LogMessageType: string
{
    case INSTALLATION_FINISHED = 'installation-finished';
    case RTC_ISSUES = 'rtc-issues';
}
