<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;

class Scene extends Model
{
    protected $casts = [
        'data' => AsArrayObject::class,
        'start_time' => 'datetime',
    ];

    public function getContainingRecordings()
    {
        return Recording::all()
            ->filter(function(Recording $recording) {
                return $this->start_time >= $recording->created_at
                    && $this->start_time <= $recording->getEndTime();
            });
    }
}
