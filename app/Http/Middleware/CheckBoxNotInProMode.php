<?php

namespace App\Http\Middleware;

use App\Support\Recorder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBoxNotInProMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Recorder::make()->inProMode()) {
            abort(404);
        }

        return $next($request);
    }
}
