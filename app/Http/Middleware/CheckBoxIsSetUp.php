<?php

namespace App\Http\Middleware;

use App\Livewire\InitialSetup;
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
        $recorder = Recorder::make();

        if($recorder->needsInitialSetup() && !$recorder->initialSetupIsRunning()) {
            return redirect()->route('initial-setup');
        }

        return $next($request);
    }
}
