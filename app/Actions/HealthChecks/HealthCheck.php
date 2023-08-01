<?php

namespace App\Actions\HealthChecks;

use App\Enums\LogMessageType;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use ReflectionClass;

abstract class HealthCheck
{
    protected LogMessageType $logMessageType;
    protected $message = '';

    abstract public function shouldReport();

    public function execute()
    {
        if($this->shouldReport()) {
            reportToMothership($this->logMessageType, $this->getMessage());
        }
    }

    protected function commandContains($command, $text)
    {
        return Str::contains(Process::run($command)->output(), $text);
    }

    protected function getMessage()
    {
        return $this->message;
    }
}
