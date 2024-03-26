<?php

namespace App\Http\Controllers\Api;

use App\Actions\EnsureAppKeyIsSet;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTokensRequest;
use App\Models\UserToken;
use App\Support\Mothership;
use App\Support\Recorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;

class RecorderController extends Controller
{
    public function iAmHere()
    {
        return 'OK';
    }

    public function info()
    {
        return [
            'system_id' => Recorder::make()->getSystemId(),
            'software_version' => Recorder::make()->currentSoftwareVersion(),
        ];
    }

    public function updateSoftware()
    {
        return app(\App\Actions\UpdateSoftware::class)
            ->execute();
    }

    public function installationFinished()
    {
        if(!Recorder::make()->installationIsFinished()) {
            Storage::put(Recorder::INSTALLATION_FINISHED_FILENAME, '');
        }

        UserToken::where('entity_id', 999999)->delete();

        return [
            'status' => 'OK',
        ];
    }

    public function refreshAppKey()
    {
        $appKeyWasSet = app(EnsureAppKeyIsSet::class)->execute();

        return [
            'app_key_refreshed' => !$appKeyWasSet,
        ];
    }

    public function getPublicKey()
    {
        return [
            'public_key' => Recorder::make()->getPublicKey(),
        ];
    }

    public function networkStatus()
    {
        return [
            'vpn' => $this->isServerReachable('10.0.0.1'),
            'mothership' => $this->isServerReachable('mothership.taggy.cam'),
            'api' => $this->isServerReachable('api-v2.taggy.cam'),
        ];
    }

    /*
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

        return [
            'status' => 'OK',
        ];
    }

    public function startVpn()
    {
        Process::run('sudo wg-quick up wg0');
        return $this->vpnStatus();
    }

    public function stopVpn()
    {
        Process::run('sudo wg-quick down wg0');
        return $this->vpnStatus();
    }
    */

    public function setDotEnv(Request $request)
    {
        DotenvEditor::setKey($request->key, $request->value);
        DotenvEditor::save();

        return [
            'status' => 'OK',
        ];
    }

    public function tokens(StoreTokensRequest $request)
    {
        foreach($request->entities as $entity) {
            UserToken::updateOrCreate([
                'entity_id' => $entity['id'],
                'user_id' => $request->user_id,
                'endpoint' => Mothership::getEndpoint(),
            ], [
                'token' => $request->token,
                'last_successfully_used_at' => $entity['last_successfully_used_at'],
                'last_rejected_at' => null,
            ]);
        }

        return ['status' => 'OK'];
    }

    private function isServerReachable($host, $timeout = 1)
    {
        exec(sprintf('ping -c 1 -W %d %s', $timeout, escapeshellarg($host)), $output, $status);

        return $status === 0;
    }
}
