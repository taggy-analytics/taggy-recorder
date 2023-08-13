<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EnableApiController
{
    const SESSION_KEY = 'api-docs-enabled';

    public function __invoke($key, Request $request)
    {
        if($key != config('taggy-recorder.enable-api-docs-key')) {
            abort(403);
        }

        session()->put(self::SESSION_KEY, true);
        return redirect()->route('scramble.docs.api');
    }
}
