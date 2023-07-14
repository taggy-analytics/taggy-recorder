<?php

namespace App\Http\Controllers\Api;

// use App\Console\Commands\FinalizeInstallation;
use App\Http\Controllers\Controller;
use App\Support\NetworkManager;
use App\Support\Recorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class WifiController extends Controller
{
    /*
    public function index()
    {
        return NetworkManager::make()->getWifis();
    }

    public function store(Request $request)
    {
        $request->validate([
            'ssid' => 'required',
            'password' => 'required|min:8',
        ]);

        NetworkManager::make()->addWifi($request->ssid, $request->password);

        if(!Recorder::make()->installationIsFinished()) {
            app()->terminating(fn() => Artisan::call(FinalizeInstallation::class));
        }
    }

    public function destroy($ssid)
    {
        NetworkManager::make()->deleteWifi($ssid);
    }

    public function updatePassword($ssid, Request $request)
    {
        $request->validate([
            'password' => 'required|min:8',
        ]);

        NetworkManager::make()->updateWifiPassword($ssid, $request->password);
    }
    */
}
