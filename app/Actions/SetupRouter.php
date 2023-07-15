<?php

namespace App\Actions;

use Illuminate\Support\Facades\Process;

class SetupRouter
{
    public function execute()
    {
        $gatewayIp = $this->getGatewayIp();

        if($gatewayIp == '192.168.222.1') {
            dump('Seems like router is setup.');
        }
        elseif($gatewayIp == '192.168.8.1') {
            $this->updateRouter();
        }
        else {
            dump('Unknown configuration.');
        }
    }

    private function updateRouter()
    {
        dump('Initial config');
    }

    private function getGatewayIp()
    {
        $output = Process::run('ip route | grep default')
            ->output();
        dump($output);
        preg_match('/default via ([0-9\.]+)/', $output, $matches);
        dump($matches[1]);
        return $matches[1];
    }
}
