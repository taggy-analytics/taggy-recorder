<?php

namespace App\Services;

use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;

class Hikvision
{
    private Factory $client;

    public function __construct($baseUrl, $username, $password)
    {
        $this->client = Http::withDigestAuth($username, $password)
            ->baseUrl($baseUrl);
    }

    public static function make($baseUrl, $username, $password)
    {
        return new self($baseUrl, $username, $password);
    }

    public function getDeviceInfo()
    {
        return $this->get('System/deviceInfo');
    }

    private function get($endpoint)
    {
        return $this->request('get', $endpoint);
    }

    private function request($method, $endpoint, $data = [])
    {
        $response = $this->client->$method($endpoint, $data);

        $xml = simplexml_load_string($response->body());

        return json_decode(json_encode($xml), true);
    }
}
