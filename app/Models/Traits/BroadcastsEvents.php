<?php

namespace App\Models\Traits;

trait BroadcastsEvents
{
    use \Illuminate\Database\Eloquent\BroadcastsEvents;

    public function broadcastOn(string $event): array
    {
        return ['recorder'];
    }
}
