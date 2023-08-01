<?php

namespace App\Actions\HealthChecks;

use App\Enums\LogMessageType;

class Rtc extends HealthCheck
{
    protected LogMessageType $logMessageType = LogMessageType::RTC_ISSUES;
    protected $message = 'RTC failure';

    public function shouldReport()
    {
        return !$this->commandContains('timedatectl', 'RTC time: n/a');
    }
}
