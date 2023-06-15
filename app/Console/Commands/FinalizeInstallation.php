<?php

namespace App\Console\Commands;

use App\Support\Mothership;
use App\Support\Recorder;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Process;

class FinalizeInstallation extends Command
{
    protected $signature = 'taggy:finalize-installation';
    protected $description = 'Finalize installation';

    private const ETHERNET_ID_PLACEHOLDER = 'ETHERNET_ID';

    public function handle()
    {
        $this->updateSystemConfiguration();
        return;





        $recorder = Recorder::make();

        if ($recorder->installationIsFinished()) {
            return 0;
        }

        if (!Mothership::make()->isOnline()) {
            $this->error('Must be connected to internet to finish installation.');
            return 1;
        }

        $this->updateSystemConfiguration();
        $this->preprovision();

        $recorder->markInstallationAsFinished();

        return 0;
    }

    private function updateSystemConfiguration()
    {
        $ethernetId = $this->getExternalEthernetInterfaceId();
        $this->replaceStringInFile('/etc/netplan/10-taggy.yaml', self::ETHERNET_ID_PLACEHOLDER, $ethernetId);
        $this->replaceStringInFile('/etc/dnsmasq.conf', self::ETHERNET_ID_PLACEHOLDER, $ethernetId);
        Process::run('sudo netplan generate');
        Process::run('sudo netplan apply');
        Process::run('sudo systemctl restart dnsmasq');
        Process::run('iptables -t nat -A POSTROUTING -o ' . $ethernetId . ' -j MASQUERADE');
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

    function replaceStringInFile($file, $search, $replace) {
        if(file_exists($file) && is_writable($file)) {
            $content = file_get_contents($file);
            $contentChunks = str_replace($search, $replace, $content);
            file_put_contents($file, $contentChunks);
        }
    }
}
