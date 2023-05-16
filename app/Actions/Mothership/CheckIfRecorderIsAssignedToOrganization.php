<?php

namespace App\Actions\Mothership;

use App\Support\Mothership;
use Illuminate\Support\Facades\Storage;

class CheckIfRecorderIsAssignedToOrganization
{
    public function execute()
    {
        $mothership = Mothership::make();

        if(!$mothership->currentRecorder()) {
            Storage::delete(Mothership::MOTHERSHIP_TOKEN_FILENAME);
        }
    }
}
