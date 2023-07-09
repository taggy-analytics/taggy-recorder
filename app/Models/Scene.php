<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;

class Scene extends Model
{
    protected $dateFormat = 'Y-m-d H:i:s.v';

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

    public function getEndTime()
    {
        return $this->start_time->addMilliseconds($this->duration * 1000);
    }

    public function videoFilePath(Recording $recording, $extension = 'mp4')
    {
        return 'scene-videos/' . $this->id . '/' . $recording->id . '/' . $this->getHash($recording) . '.' . $extension;
    }

    private function getHash(Recording $recording = null)
    {
        return md5(serialize([
            $this->id,
            $recording?->id,
            $this->start_time,
            $this->duration,
        ]));
    }
}
