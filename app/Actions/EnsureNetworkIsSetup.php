<?php

namespace App\Actions;

use App\Enums\LogMessageType;
use App\Services\GliNet;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
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
        try {
            $clients = GliNet::make()
                ->getClients();
        }
        catch(ConnectionException $exception) {
            return null;
        }

        return $clients
            ->filter(fn($client) => Arr::get($client, 'online') === true)
            ->pluck("name")
            ->contains(gethostname());
    }
}
