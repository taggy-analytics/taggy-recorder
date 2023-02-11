<?php

namespace App\Actions\Mothership;

use App\Models\Camera;

class SendDiscoveredCamerasToMothership
{
    protected function execute()
    {
        foreach(Camera::whereNull('sent_to_mothership_at')->get() as $camera) {
            app(SendDiscoveredCameraToMothership::class)->execute($camera);
        }
    }
}
