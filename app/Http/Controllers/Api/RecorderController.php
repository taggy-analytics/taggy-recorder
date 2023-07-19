<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Recorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

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

    public function setVpnConfig(Request $request)
    {
        File::put('/etc/wireguard/wg0.conf', $request->vpnConfig);
    }
}
