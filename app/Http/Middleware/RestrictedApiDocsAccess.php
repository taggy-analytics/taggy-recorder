<?php

namespace App\Http\Middleware;

use App\Http\Controllers\EnableApiController;

class RestrictedApiDocsAccess
{
    public function handle($request, \Closure $next)
    {
        if (app()->environment('local')) {
            return $next($request);
        }

        if (session()->get(EnableApiController::SESSION_KEY)) {
            return $next($request);
        }

        abort(403);
    }
}
