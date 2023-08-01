<?php

namespace App\Actions;

use App\Actions\HealthChecks\Rtc;

class RunHealthChecks
{
    public function execute()
    {
        app(Rtc::class)->execute();
    }
}
