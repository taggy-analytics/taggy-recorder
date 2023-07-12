<?php

namespace App\Console\Commands;

class RunMothershipActions extends PseudoDaemon
{
    protected $signature = 'taggy:run-mothership-actions';
    protected $description = 'Run mothership actions';
    protected $action = \App\Actions\Mothership\RunMothershipActions::class;
}
