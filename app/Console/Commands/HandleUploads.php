<?php

namespace App\Console\Commands;

class HandleUploads extends PseudoDaemon
{
    protected $signature = 'taggy:handle-uploads';
    protected $description = 'Handle uploads';
    protected $action = \App\Actions\HandleUploads::class;
    protected $sleepSeconds = 20;
}
