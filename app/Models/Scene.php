<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;

class Scene extends Model
{
    protected $casts = [
        'data' => 'array',
        'start_time' => 'datetime',
    ];

    public function getContainingRecordings()
    {
        return Recording::all()
            ->filter(function(Recording $recording) {
                return $this->start_time >= $recording->started_at
                    && $this->start_time <= $recording->getEndTime();
            });
    }

    public function videoFilePath(Recording $recording)
    {
        return 'scene-videos/' . $this->id . '-' . $recording->id . '.mp4?v=' . substr(md5($this->start_time->toDateTimeString('millisecond') . $this->duration), 0, 6);
    }
}
