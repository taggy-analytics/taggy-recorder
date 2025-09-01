<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckSoftware extends Command
{
    protected $signature = 'taggy:check-software {path?}';

    protected $description = 'Checks software for integrity';

    private $path;

    public function handle()
    {
        $this->path = $this->argument('path') ?? '/var/www/taggy/current';

        chdir($this->path);

        $errors = [];

        foreach ($this->checks() as $name => $check) {
            if ($check()) {
                $this->info($name.' succeeded.');
            } else {
                $errors[] = $name;
                $this->error($name.' failed.');
            }
        }

        if (count($errors) > 0) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function checks()
    {
        return [
            'vendorFolderExists' => fn () => File::exists($this->path.'/vendor'),
            'ledPyFileSize' => fn () => File::size($this->path.'/led.py') > 0,
        ];
    }
}
