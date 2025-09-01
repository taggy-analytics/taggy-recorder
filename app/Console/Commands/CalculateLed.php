<?php

namespace App\Console\Commands;

class CalculateLed extends PseudoDaemon
{
    protected $signature = 'taggy:calculate-led';

    protected $description = 'Calculate LED';

    protected $action = \App\Actions\CalculateLed::class;
}
