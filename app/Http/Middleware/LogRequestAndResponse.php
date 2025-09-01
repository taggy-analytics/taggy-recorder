<?php

// app/Http/Middleware/LoggerMiddleware.php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequestAndResponse
{
    /**
     * Handle an incoming request.
     *
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! config('taggy-recorder.log-requests')) {
            return $next($request);
        }

        $response = $next($request);

        $contents = json_decode($response->getContent(), true, 512);

        $dt = new Carbon;
        $data = [
            'path' => $request->getPathInfo(),
            'method' => $request->getMethod(),
            'ip' => $request->ip(),
            'http_version' => $_SERVER['SERVER_PROTOCOL'],
            'timestamp' => $dt->toDateTimeString(),
            'headers' => $request->headers->all(),
        ];

        // if request if authenticated
        if ($request->user()) {
            $data['user_id'] = $request->user()->id;
        }

        // if you want to log all the request body
        if (count($request->all()) > 0) {
            // keys to skip like password or any sensitive information
            $hiddenKeys = ['password'];

            $data['request'] = $request->except($hiddenKeys);
        }

        // to log the message from the response
        if (! empty($contents['message'])) {
            $data['response']['message'] = $contents['message'];
        }

        // to log the errors from the response in case validation fails or other errors get thrown
        if (! empty($contents['errors'])) {
            $data['response']['errors'] = $contents['errors'];
        }

        // to log the data from the response, change the RESULT to your API key that holds data
        if (! empty($contents['result'])) {
            $data['response']['result'] = $contents['result'];
        }

        // a unique message to log, I prefer to save the path of request for easy debug
        $message = str_replace('/', '_', trim($request->getPathInfo(), '/'));

        // log the gathered information
        ray($message, $data, $response);

        // return the response
        return $response;
    }
}
