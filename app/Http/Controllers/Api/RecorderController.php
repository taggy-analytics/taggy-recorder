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

    public function refreshAppKey()
    {
        $appKeyWasSet = app(EnsureAppKeyIsSet::class)->execute();

        UserToken::truncate();

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
            'mothership' => $this->isServerReachable('mothership.taggy.cam'),
            'api' => $this->isServerReachable('api-v2.taggy.cam'),
        ];
    }

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
