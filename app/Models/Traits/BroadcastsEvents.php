<?php

namespace App\Models\Traits;

use App\Http\Resources\CameraResource;
use App\Models\Camera;
use App\Models\Recording;
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
        $resource = 'App\\Http\\Resources\\' . (new \ReflectionClass($this))->getShortName() . 'Resource';
        return $resource::make($this);
    }
}
