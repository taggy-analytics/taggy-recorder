<?php

namespace App\Actions\Mothership;

use App\Exceptions\MothershipException;
use App\Support\Mothership;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Storage;

abstract class MothershipAction
{
    protected Mothership $mothership;

    abstract protected function executeAction();

    public function execute(...$attributes)
    {
        if(!Storage::exists(Mothership::MOTHERSHIP_TOKEN_FILENAME)) {
            return;
        }

        $this->mothership = Mothership::make();

        try {
            if ($mothershipStatus = $this->mothership->checkStatus()) {
                if ($mothershipStatus->status() != 200) {
                    info('Mothership is not reachable. Status: ' . $mothershipStatus->status());
                    return;
                }
            }
        }
        catch(ConnectionException $exception) {
            // Timeout
            return;
        }

        return $this->executeAction(...$attributes);
    }
}
