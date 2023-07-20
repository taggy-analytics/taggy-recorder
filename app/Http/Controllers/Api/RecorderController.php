<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Recorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class RecorderController extends Controller
{
    public function systemId()
    {
        return Recorder::make()->getSystemId();
    }

    public function updateSoftware()
    {
        return app(\App\Actions\UpdateSoftware::class)
            ->execute();
    }

    public function vpnStatus()
    {
        $output = Process::run("ip link show wg0 2>&1")->output();

        return [
            'connected' => !Str::contains($output, 'does not exist'),
        ];
    }

    public function setVpnConfig(Request $request)
    {
        Process::run('sudo -S bash -c \'echo "' . $request->get('config') . '" > /etc/wireguard/wg0.conf\'');
    }

    public function startVpn()
    {
        Process::run('sudo wg-quick up wg0');
    }

    public function stopVpn()
    {
        Process::run('sudo wg-quick down wg0');
    }
}
