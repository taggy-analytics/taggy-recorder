<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class HandleUploadRequests extends PseudoDaemon
{
    protected $signature = 'taggy:handle-upload-requests';
    protected $description = 'Handle upload requests';
    protected $action = \App\Actions\HandleUploadRequests::class;
    protected $sleepSeconds = 30;
}
