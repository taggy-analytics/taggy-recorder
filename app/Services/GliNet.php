<?php

namespace App\Services;

use App\Http\Integrations\GliNet\GliNetConnector;
use App\Http\Integrations\GliNet\Requests\ClientListRequest;

class GliNet
{
    private $connector;

    public static function make()
    {
        return new self;
    }

    public function __construct()
    {
        $this->connector = new GliNetConnector();
    }

    public function clients()
    {
        return $this->send(new ClientListRequest());
    }

    private function send($request)
    {
        return collect($this->connector->send(new ClientListRequest())
            ->json('clients'));
    }
}
