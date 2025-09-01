<?php

namespace App\Console\Commands;

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
