<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class MothershipException extends Exception
{
    public function __construct(
        public $method,
        public $url,
        public $data,
        public $response,
    ) {
        Log::channel('mothership')->error($response?->getBody());
        parent::__construct($response?->status().': '.$method.' '.$url);
    }
}
