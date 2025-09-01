<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetEnvironment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:env {environment}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set recorder environment';

    /**
     * ra
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (! in_array($this->argument('environment'), ['production', 'local', 'demo', 'test'])) {
            $this->error("{$this->argument('environment')} is not a known environment.");

            return 1;
        }

        if ($this->argument('environment') == config('app.env')) {
            $this->warn("Recorder is already running on {$this->argument('environment')}.");

            return 1;
        }

        if ($this->confirm('This deletes all data and all recordings! Are you sure?')) {
            app(\App\Actions\SetEnvironment::class)->execute($this->argument('environment'));
            $this->info('Finished!');
        }
    }
}
