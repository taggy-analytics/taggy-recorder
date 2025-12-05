<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class Hikvision
{
    private PendingRequest $client;

    public function __construct($ipAddress, $username, $password)
    {
        $this->client = Http::withDigestAuth($username, $password)
            ->baseUrl('http://' . $ipAddress . '/ISAPI');
    }

    public static function make($ipAddress, $username, $password)
    {
        return new self($ipAddress, $username, $password);
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
