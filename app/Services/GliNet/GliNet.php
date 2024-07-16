<?php

namespace App\Services\GliNet;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GliNet
{
    public function __construct(private $module) {}

    public static function __callStatic($name, $arguments)
    {
        return new self(Str::snake($name, '-'));
    }

    public function __call($name, $arguments)
    {
        return $this->call(Str::snake($name), ...$arguments);
    }

    private function call($function, $params = [])
    {
        return $this->send('call', [
            $this->getSid(),
            $this->module,
            $function,
            $params,
        ]);
    }

    private function send($method, $params)
    {
        throw new \Exception('This does not work any more and should not be needed currently. If it is still needed, the endpoint need to be calculated from Network::getGateway');
        $response = Http::post(config('services.glinet.endpoint'), [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => 0,
        ])->json();

        return Arr::get($response, 'result');
    }

    private function getSid()
    {
        return blink()->once('glinet-sid', function() {
            $username = config('services.glinet.username');
            $password = config('services.glinet.password');

            $data = $this->send('challenge', [
                'username' => $username,
            ]);

            $cipherPassword = match ($data['alg']) {
                1 => crypt($password, '$1$' . $data['salt']),
                5 => crypt($password, '$5$rounds=5000$' . $data['salt']),
                6 => crypt($password, '$6$rounds=5000$' . $data['salt']),
            };

            $hash = md5("{$username}:{$cipherPassword}:{$data['nonce']}");

            return $this->send('login', [
                'username' => $username,
                'hash' => $hash,
            ])['sid'];
        });
    }
}
