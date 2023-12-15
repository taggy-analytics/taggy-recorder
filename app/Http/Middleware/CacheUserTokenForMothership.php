<?php

namespace App\Http\Middleware;

use App\Support\Mothership;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheUserTokenForMothership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        cache()->put('user-token', $request->header('User-Token'), now()->addMinutes(10));
        cache()->put('mothership-endpoint', Mothership::getEndpoint(), now()->addMinutes(10));
        return $next($request);
    }
}
