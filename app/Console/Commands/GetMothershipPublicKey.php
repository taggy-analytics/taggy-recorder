<?php

namespace App\Console\Commands;

use App\Support\PublicKey;
use Illuminate\Console\Command;

class GetMothershipPublicKey extends Command
{
    protected $signature = 'taggy:get-mothership-public-key';

    protected $description = 'Get and store the public key for the mothership';

    public function handle()
    {
        $environment = [
            'key' => 'production',
            'urls' => [
                'api' => 'https://api-v2.taggy.cam',
            ],
        ];

        PublicKey::get($environment);
    }
}
