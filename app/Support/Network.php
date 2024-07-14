<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Process;

class Network
{
    public static function make()
    {
        return new self();
    }

    public function getClients()
    {
        $nmap = Process::run('sudo nmap -sn -PR -T5 -oX - 10.3.16.0/24')
            ->output();

        $xml = simplexml_load_string($nmap);

        $json = json_encode($xml);
        $array = json_decode($json, true);

        return collect($array["host"])
            ->map(function ($host) {
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
                return !empty($client["identifier"]) && !empty($client["ipAddress"]);
            })
            ->values();
    }
}
