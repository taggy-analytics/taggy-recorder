<?php

namespace App\Actions;

use App\Enums\LogMessageType;
use Illuminate\Support\Facades\Process;

class EnsureNetworkIsSetup
{
    public function execute()
    {
        $counter = 0;

        while(!$this->routerKnowsHostname()) {
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

    private function routerKnowsHostname()
    {
        return strpos(shell_exec("ping -c 1 " . config('taggy-recorder.hostname')), '1 received') !== false;
    }
}
