<?php

namespace App\Http\Middleware;

use App\Support\Mothership;
use Closure;
use Illuminate\Http\Request;
use Spatie\Crypto\Rsa\PublicKey;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserAuthenticatedAgainstMothership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // We don't need encrypted user data currently
        // We just have to make sure that the client authenticated against the mothership before
        if(!Mothership::make()->validateUserData($request->header('User-Data'))) {
            abort(403);
        }

        cache()->put('user-token', $request->header('User-Token'), now()->addMinutes(10));
        return $next($request);
    }
}
