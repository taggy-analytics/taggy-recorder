<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TruncateRecorder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:truncate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate recorder';

    /**
     * ra
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->confirm('This deletes all relevant data and recordings! Are you sure?')) {
            app(\App\Actions\TruncateRecorder::class)->execute();
            $this->info('Finished!');
        }
    }
}
