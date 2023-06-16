<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Yaml\Yaml;

class NetworkManager
{
    //private const WIFI_CONFIG_PATH = '/etc/netplan/20-wifis.yaml';
    private const WIFI_CONFIG_PATH = '/Users/seb/dev/taggy-recorder/storage/app/wifis.yaml';

    public static function make()
    {
        return new self;
    }

    public function getWifis()
    {
        $config = $this->readWifiConfig();
        return array_keys(Arr::get($config, 'network.wifis.wlan0.access-points', []));
    }

    public function addWifi($ssid, $password)
    {
        $config = $this->readWifiConfig();
        $config['network']['wifis']['wlan0']['access-points'][$ssid] = ['password' => $password];
        $this->writeWifiConfig($config);

        $this->applyNetworkConfig();
    }

    public function updateWifiPassword($ssid, $password)
    {
        $config = $this->readWifiConfig();
        if(Arr::has($config['network']['wifis']['wlan0']['access-points'], $ssid)) {
            $config['network']['wifis']['wlan0']['access-points'][$ssid]['password'] = $password;
            $this->writeWifiConfig($config);

            $this->applyNetworkConfig();
        }
    }

    public function deleteWifi($ssid)
    {
        $config = $this->readWifiConfig();
        unset($config['network']['wifis']['wlan0']['access-points'][$ssid]);
        $this->writeWifiConfig($config);

        $this->applyNetworkConfig();
    }

    private function readWifiConfig()
    {
        if(!File::exists(self::WIFI_CONFIG_PATH)) {
            File::copy(resource_path('installation/20-wifis.yaml'), self::WIFI_CONFIG_PATH);
        }

        $yaml = File::get(self::WIFI_CONFIG_PATH);
        return Yaml::parse($yaml);
    }

    private function writeWifiConfig($config)
    {
        File::put(self::WIFI_CONFIG_PATH, Yaml::dump($config, 6, 2));
    }

    public function applyNetworkConfig()
    {
        Process::run('sudo netplan generate');
        Process::run('sudo netplan apply');
    }
}
