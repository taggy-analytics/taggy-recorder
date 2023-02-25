<?php

namespace App\Console\Commands;

use Hammerstone\PseudoDaemon\IsPseudoDaemon;
use Illuminate\Console\Command;

class HandleCameras extends PseudoDaemon
{
    protected $signature = 'taggy:handle-cameras';
    protected $description = 'Handle cameras';
    protected $action = \App\Actions\HandleCameras::class;
    protected $sleepSeconds = 5;
}
