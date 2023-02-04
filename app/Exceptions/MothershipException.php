<?php

namespace App\Exceptions;

use Exception;

class MothershipException extends Exception
{
    public function __construct($response, $method, $url)
    {
        parent::__construct($response?->status() . ': ' . $method . ' ' . $url);
    }
}
