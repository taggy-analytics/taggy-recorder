<?php

namespace App\Services;

use App\Http\Integrations\GliNet\GliNetConnector;
use App\Http\Integrations\GliNet\Requests\ClientListRequest;
use Illuminate\Support\Arr;

class GliNet
{
    private $connector;

    public static function make(): GliNet
    {
        return new self;
    }

    public function __construct()
    {
        $this->connector = new GliNetConnector();
    }

    public function clients()
    {
        return collect(Arr::get($this->send(new ClientListRequest()), 'clients'));
    }

    private function send($request)
    {
        return $this->connector
            ->send($request)
            ->json();
    }
}
