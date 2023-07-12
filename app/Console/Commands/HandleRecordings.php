<?php

namespace App\Console\Commands;

class HandleRecordings extends PseudoDaemon
{
    protected $signature = 'taggy:handle-recordings';
    protected $description = 'Handle recordings';
    protected $action = \App\Actions\HandleRecordings::class;
}
