<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Recorder;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CheckBoxIsSetUp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(Recorder::make()->needsInitialSetup() && !Str::endsWith($request->header('referer'), '/initial-setup')) {
            return redirect()->route('initial-setup');
        }

        return $next($request);
    }
}
