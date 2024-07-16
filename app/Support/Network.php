<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;

class Network
{
    public static function make()
    {
        return new self();
    }

    public function getClients() : Collection
    {
        $nmap = Process::run('sudo nmap -sn -PR -T5 -oX - ' . $this->getSubnet())
            ->output();

        $xml = simplexml_load_string($nmap);

        $json = json_encode($xml);
        $array = json_decode($json, true);

        return collect(Arr::get($array, "host"))
            ->map(function ($host) {
                if(!Arr::has($host, "address")) {
                    return [
                        'identifier' => null,
                    ];
                }

                if (Arr::has($host["address"], "@attributes")) {
                    $host["address"] = [$host["address"]];
                }

                $addresses = Arr::flatten($host["address"], 1);

                return [
                    "identifier" => Arr::get(
                        collect($addresses)->firstWhere("addrtype", "mac"),
                        "addr"
                    ),
                    "name" => Arr::get($host, "hostnames.hostname.@attributes.name"),
                    "ipAddress" => Arr::get(
                        collect($addresses)->firstWhere("addrtype", "ipv4"),
                        "addr"
                    )
                ];
            })
            ->filter(function ($client) {
                return !empty($client["ipAddress"]);
            })
            ->values();
    }

    private function getSubnet()
    {
        $command = "ip -o -f inet addr show | awk '/scope global/ {print $4}'";

        $cidr = Process::run($command)->output();

        list($ip, $netmask) = explode("/", trim($cidr));

        $ipParts = explode(".", $ip);
        $subnetStart = implode(".", array_slice($ipParts, 0, -1)) . ".0";

        return $subnetStart . "/" . $netmask;
    }
}
