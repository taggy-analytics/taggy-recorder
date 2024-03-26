<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GliNet\GliNet;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class WifiController extends Controller
{
    public function index()
    {
        return Arr::get(GliNet::repeater()->getSavedApList(), 'res');
    }

    public function scan()
    {
        return Arr::get(GliNet::repeater()->scan(), 'res');
    }

    public function connect(Request $request)
    {
        $request->validate([
            'ssid' => 'required',
            'key' => 'required',
        ]);

        return GliNet::repeater()->connect([
            'ssid' => $request->ssid,
            'key' => $request->key,
            'remember' => true,
        ]);
    }

    public function disconnect()
    {
        return GliNet::repeater()->disconnect();
    }

    public function delete(Request $request)
    {
        $request->validate([
            'ssid' => 'required',
        ]);

        return GliNet::repeater()->removeSavedAp([
            'ssid' => $request->ssid,
        ]);
    }
}
