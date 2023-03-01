<?php

namespace App\Actions\Mothership;

use App\Models\Camera;
use App\Support\Mothership;

class SendDiscoveredCamerasToMothership
{
    public function execute()
    {
        foreach(Camera::whereNull('sent_to_mothership_at')->get() as $camera) {
            Mothership::make()->reportDiscoveredCamera($camera);
            $camera->update(['sent_to_mothership_at' => now()]);
        }
    }
}
