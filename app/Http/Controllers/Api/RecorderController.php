<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Recorder;

class RecorderController extends Controller
{
    public function systemId()
    {
        return Recorder::make()->getSystemId();
    }

    public function updateSoftware()
    {
        return app(\App\Actions\UpdateSoftware::class)
            ->execute();
    }
}
