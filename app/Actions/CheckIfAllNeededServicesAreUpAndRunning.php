<?php

namespace App\Actions;

use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\RedisCheck;
use Spatie\Health\Enums\Status;

class CheckIfAllNeededServicesAreUpAndRunning
{
    public function execute()
    {
        $checks = [
            DatabaseCheck::new(),
            RedisCheck::new(),
        ];

        foreach ($checks as $check) {
            if ($check->run()->status !== Status::ok()) {
                return false;
            }
        }

        return true;
    }
}
