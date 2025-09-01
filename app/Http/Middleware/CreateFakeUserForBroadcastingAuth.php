<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateFakeUserForBroadcastingAuth
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        if (auth()->guest()) {
            auth()->login(new User);
        }

        return $next($request);
    }
}
