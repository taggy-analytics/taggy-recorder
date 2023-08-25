<?php

namespace App\Http\Controllers\Api;

use App\Actions\Mothership\SyncTransactionsWithMothership;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestingController extends Controller
{
    public function connectedToMothership(Request $request)
    {
        cache()->put('connectedToMothership', $request->connected, now()->addDay());

        if($request->connected) {
            app(SyncTransactionsWithMothership::class)
                ->execute();
        }
    }
}
