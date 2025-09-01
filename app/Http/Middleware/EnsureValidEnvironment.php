<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidEnvironment
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $configuredEnv = config('app.env');
        if ($request->environmentData()['key'] !== $configuredEnv) {
            abort(421, "You are logged in at the {$request->environmentData()['key']} system and therefore can’t use this recorder which is registered at the {$configuredEnv} system.");
        }

        return $next($request);
    }
}
