<?php

namespace App\Console\Commands;

use App\Enums\LogMessageType;
use App\Support\Mothership;
use App\Support\NetworkManager;
use App\Support\Recorder;
use Illuminate\Console\Command;
use Illuminate\Foundation\Console\KeyGenerateCommand;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class FinalizeInstallation extends Command
{
    /*
    protected $signature = 'taggy:finalize-installation';
    protected $description = 'Finalize installation';

    private const ETHERNET_ID_PLACEHOLDER = 'ETHERNET_ID';

    public function handle()
    {
        $recorder = Recorder::make();

        if ($recorder->installationIsFinished()) {
            return 0;
        }

        if (!Mothership::make()->isOnline()) {
            $this->error('Must be connected to internet to finish installation.');
            return 1;
        }

        // $this->call(KeyGenerateCommand::class, ['--force' => true]);
        //$this->preprovision();
        //$this->updateSystemConfiguration();

        $recorder->markInstallationAsFinished();
        reportToMothership(LogMessageType::INSTALLATION_FINISHED);

        return 0;
    }

    private function updateSystemConfiguration()
    {
        $ethernetId = $this->getExternalEthernetInterfaceId();

        File::move('/etc/netplan/50-cloud-init.yaml', '/etc/netplan/50-cloud-init.yaml.backup');

        Process::run('sudo chmod 777 /etc/netplan');
        if($ethernetId) {
            File::put('/etc/netplan/10-taggy.yaml', str_replace(self::ETHERNET_ID_PLACEHOLDER, $ethernetId, File::get(resource_path('installation/10-taggy-with-ethernet.yaml'))));
        }
        else {
            File::put('/etc/netplan/10-taggy.yaml', File::get(resource_path('installation/10-taggy.yaml')));
        }
        Process::run('sudo chmod 755 /etc/netplan');
        Process::run('sudo chown root:root /etc/netplan/10-taggy.yaml');
        Process::run('sudo chmod 600 /etc/netplan/10-taggy.yaml');

        Process::run('sudo chmod 666 /etc/dnsmasq.conf');
        if($ethernetId) {
            File::put('/etc/dnsmasq.conf', str_replace(self::ETHERNET_ID_PLACEHOLDER, $ethernetId, File::get(resource_path('installation/dnsmasq.conf'))));
        }
        else {
            File::put('/etc/dnsmasq.conf', str_replace('except-interface=' . self::ETHERNET_ID_PLACEHOLDER, '', File::get(resource_path('installation/dnsmasq.conf'))));
        }
        Process::run('sudo chmod 644 /etc/dnsmasq.conf');

        if($ethernetId) {
            Process::run('iptables -t nat -A POSTROUTING -o ' . $ethernetId . ' -j MASQUERADE');
            Process::run('sudo iptables-save');
        }

        NetworkManager::make()->applyNetworkConfig();
    }

    private function preprovision()
    {
        $this->call(Preprovision::class);
    }

    private function getExternalEthernetInterfaceId()
    {
        $process = Process::run('ip a');
        $pattern = '/\d: ([a-z0-9]+):/i';

        preg_match_all($pattern, $process->output(), $matches);
        $interfaces = $matches[1];

        $exclude = ['lo', 'eth0', 'wlan0', 'br0'];

        $finalResult = array_diff($interfaces, $exclude);

        return Arr::first($finalResult);
    }
    */
}
