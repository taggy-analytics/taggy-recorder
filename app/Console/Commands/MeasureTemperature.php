<?php

namespace App\Console\Commands;

class MeasureTemperature extends PseudoDaemon
{
    protected $signature = 'taggy:measure-temperature';
    protected $action = \App\Actions\MeasureTemperature::class;
}
