<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LivestreamSegment extends Model
{
    protected $casts = [
        'uploaded_at' => 'datetime',
    ];
    
    public function getRecording()
    {
        $recordingId = array_slice(explode("/", $this->file), -4, 1)[0];
        return Recording::find($recordingId);
    }
}
