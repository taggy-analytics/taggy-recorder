<?php

namespace App\Console\Commands;

class MonitorRecordings extends PseudoDaemon
{
    protected $signature = 'taggy:monitor-recordings';

    protected $description = 'Monitor recordings';

    protected $action = \App\Actions\MonitorRecordings::class;
}
