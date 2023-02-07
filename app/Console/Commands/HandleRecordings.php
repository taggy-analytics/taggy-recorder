<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class HandleRecordings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:handle-recordings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle recordings';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        app(\App\Actions\WatchRecordedFiles::class)
            ->execute();

        return 0;
    }
}
