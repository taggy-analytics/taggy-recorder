<?php

namespace App\Support;

use App\Enums\RecordingStatus;
use App\Exceptions\MothershipException;
use App\Models\Recording;
use App\Models\RecordingFile;
use App\Models\UserToken;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\LaravelIgnition\Facades\Flare;

class PublicKey extends \Spatie\Crypto\Rsa\PublicKey
{
    public static function get()
    {
        $environment = json_decode(base64_decode(request()->header('Environment-Data')), true);
        $keyPath = 'keys/mothership-' . $environment['key'] . '-public.key';

        if(!Storage::has($keyPath)) {
            Storage::put($keyPath, Http::baseUrl($environment['urls']['api'])
                ->get('v1/public-key')['public_key']);
        }

        return self::fromFile(Storage::path($keyPath));
    }
}
