<?php

namespace App\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class EnsureNetworkIsSetup
{
    public function execute()
    {
        if(!$this->ensureHostnameIsSet()) {
            info('Hostname is not set.');
        }
    }

    private function ensureHostnameIsSet()
    {
        $counter = 0;
        do {
            $eth0IpAdress = $this->getInterfaceIpAddress();

            $output = Process::run('nslookup ' . $eth0IpAdress)
                ->output();

            if(Str::contains($output, $this->getHostname())) {
                return true;
            }

            Process::run('sudo ip link set eth0 down');
            sleep(1);
            Process::run('sudo ip link set eth0 up');

            $counter++;
            sleep(3);
        } while ($counter < 10);

        return false;
    }

    private function getInterfaceIpAddress($interface = 'eth0')
    {
        $output = Process::run('ip a')
            ->output();

        $pattern = '/2: ' . $interface . '.*\n.*\n.*inet ([\d\.]+)/';

        preg_match($pattern, $output, $matches);

        return Arr::get($matches, 1);
    }

    private function getHostname()
    {
        return parse_url(config('app.url'), PHP_URL_HOST);
    }
}
