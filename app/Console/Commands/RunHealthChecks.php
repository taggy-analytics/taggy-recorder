<?php

namespace App\Console\Commands;

use App\Actions\EnsureAppKeyIsSet;
use App\Actions\EnsureNetworkIsSetup;
use App\Actions\HealthChecks\HealthCheck;
use Illuminate\Console\Command;

class RunHealthChecks extends Command
{
    protected $signature = 'taggy:run-health-checks';
    protected $description = 'Run health checks';

    public function handle()
    {
        app(\App\Actions\RunHealthChecks::class)->execute();
    }
}
