<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Mothership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganizationController extends Controller
{
    public function setToken(Request $request)
    {
        info($request->all());
        Storage::put(Mothership::MOTHERSHIP_TOKEN_FILENAME, $request->token);

        // Get camera info
    }
}
