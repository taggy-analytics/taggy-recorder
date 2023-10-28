<?php

namespace App\Console\Commands;

class FreeDiskSpace extends PseudoDaemon
{
    protected $signature = 'taggy:free-disk-space';
    protected $action = \App\Actions\FreeDiskSpace::class;
}
