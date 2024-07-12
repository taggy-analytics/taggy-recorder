<?php

namespace App\Support\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;

trait BashMigration
{
    protected function removeFile($path)
    {
        Process::run('sudo rm ' . $path);
    }

    protected function writeFileIfNotExists($path, $content)
    {
        if(!File::exists($path)) {
            $escapedContent = escapeshellarg($content);
            $command = "echo $escapedContent | sudo tee $path";
            shell_exec($command);
        }
    }

    protected function updateSupervisor()
    {
        Process::run('sudo supervisorctl reread');
        Process::run('sudo supervisorctl update');
    }

    protected function createOrUpdateDotEnvValue($key, $value)
    {
        DotenvEditor::setKey($key, $value);
    }
}
