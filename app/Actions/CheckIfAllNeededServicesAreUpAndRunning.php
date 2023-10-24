<?php

namespace App\Actions;

use App\Enums\TransactionAction;
use App\Models\Transaction;
use App\Support\Recorder;
use Illuminate\Support\Arr;
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

        foreach($checks as $check) {
            if($check->run()->status !== Status::ok()) {
                return false;
            }
        }

        if(!Recorder::make()->installationIsFinished()) {
            return false;
        }

        return true;
    }
}
