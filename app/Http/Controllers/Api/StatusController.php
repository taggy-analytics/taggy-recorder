<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Recorder;

class StatusController extends Controller
{
    public function getStatus()
    {
        return [
            'systemId' => Recorder::make()->getSystemId(),
            'publicKey' => Recorder::make()->getPublicKey(),
        ];
    }
}
