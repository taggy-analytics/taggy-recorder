<?php

namespace App\Models\Traits;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Arr;

trait BroadcastsEvents
{
    use \Illuminate\Database\Eloquent\BroadcastsEvents;

    public function broadcastOn(string $event): array
    {
        return [new PrivateChannel('recorder')];
    }

    public function broadcastWith(string $event): array
    {
        if(Arr::wrap($this->broadcastAttributes) == ['*']) {
            return $this->toArray();
        }

        return Arr::only($this->toArray(), $this->broadcastAttributes);
    }
}
