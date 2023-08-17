<?php

namespace App\Exceptions;

use Exception;

class MothershipException extends Exception
{
    public function __construct(
        public $method,
        public $url,
        public $data,
        public $response,
    ){
        parent::__construct($response?->status() . ': ' . $method . ' ' . $url);
    }
}
