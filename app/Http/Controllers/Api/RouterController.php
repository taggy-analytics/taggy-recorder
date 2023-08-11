<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Recorder;

class RouterController extends Controller
{
    public function getPassword()
    {
        return [
            'password' => Recorder::make()->getRouterPassword(),
        ];
    }
}
