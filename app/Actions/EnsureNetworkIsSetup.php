<?php

namespace App\Actions;

use App\Enums\LogMessageType;
use App\Support\Network;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class EnsureNetworkIsSetup
{
    public function execute()
    {
        $counter = 0;

        while(!$this->hostnameIsKnown()) {
            Process::run('sudo ip link set eth0 down');
            sleep(1);
            Process::run('sudo ip link set eth0 up');
            sleep(3);
            $counter++;

            if($counter >= 5) {
                reportToMothership(LogMessageType::HOSTNAME_NOT_RESOLVABLE);
                return;
            }
        }
    }

    private function hostnameIsKnown()
    {
        $record = dns_get_record(config('taggy-recorder.hostname'));

        if(count($record) == 0) {
            return false;
        }

        if(Str::startsWith(Arr::get($record, '0.ip'), '127.0.')) {
            return false;
        }

        return true;
    }
}
