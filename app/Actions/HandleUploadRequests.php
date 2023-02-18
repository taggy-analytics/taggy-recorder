<?php

namespace App\Actions;

use App\Support\Mothership;

class HandleUploadRequests
{
    public function execute()
    {
        $mothership = Mothership::make();

        info($mothership->getUploadRecordingRequests());
    }
}
