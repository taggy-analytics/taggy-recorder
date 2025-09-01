<?php

namespace App\Console\Commands;

class DiscoverNewCameras extends PseudoDaemon
{
    protected $signature = 'taggy:discover-cameras';

    protected $description = 'Discover cameras';

    protected $action = \App\Actions\DiscoverNewCameras::class;
}
