<?php

namespace App\Actions;

use App\Actions\HealthChecks\HealthCheck;
use App\Actions\HealthChecks\Rtc;
use ReflectionClass;

class RunHealthChecks
{
    public function execute()
    {
        app(Rtc::class)->execute();
    }
}
