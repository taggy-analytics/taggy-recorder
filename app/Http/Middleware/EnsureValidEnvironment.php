<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidEnvironment
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        info($request->environmentData());

        return $next($request);
    }
}
