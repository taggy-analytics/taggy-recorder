<?php

namespace App\Actions\Mothership;

use App\Models\Camera;

class SendDiscoveredCamerasToMothership extends MothershipAction
{
    protected function executeAction()
    {
        foreach(Camera::whereNull('sent_to_mothership_at')->get() as $camera) {
            $this->mothership->reportDiscoveredCamera($camera);
            $camera->update(['sent_to_mothership_at' => now()]);
        }
    }
}
