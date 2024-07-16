<?php

namespace App\Actions;

use App\Enums\LogMessageType;
use App\Support\Network;
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
        return Network::make()
            ->getClients()
            ->pluck('name')
            ->filter(fn($name) => Str::startsWith($name, config('taggy-recorder.hostname')))
            ->count() > 0;
    }
}
