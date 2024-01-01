<?php

namespace App\Services;

use App\Services\GliNet\GliNet as GliNetApi;

class GliNet
{
    public static function make()
    {
        return new self();
    }

    public function getClients()
    {
        return GliNetApi::client()->getList();
    }
}
