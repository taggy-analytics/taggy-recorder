<?php

namespace App\Http\Middleware;

use App\Actions\SetSystemTime;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRecorderTimeIsCorrect
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        if($request->hasHeader('device-time')) {
            $timeDiffBetweenDeviceAndRecorder = abs(Carbon::parse($request->header('device-time'))->diffInMilliseconds());

            if($timeDiffBetweenDeviceAndRecorder > config('taggy-recorder.date-time-tolerance')) {
                app(SetSystemTime::class)->execute(CarbonImmutable::parse($request->header('device-time')));
            }
        }

        return $next($request);
    }
}
