<?php

namespace App\Console\Commands;

use App\Events\TransactionsRecalculated;
use Illuminate\Console\Command;

class SendTestBroadcastEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:send-test-broadcast-event {entityId=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test broadcast event';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        TransactionsRecalculated::dispatch($this->argument('entityId'));
    }
}
