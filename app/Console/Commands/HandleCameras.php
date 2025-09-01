<?php

namespace App\Console\Commands;

class HandleCameras extends PseudoDaemon
{
    protected $signature = 'taggy:handle-cameras';

    protected $description = 'Handle cameras';

    protected $action = \App\Actions\HandleCameras::class;
}
