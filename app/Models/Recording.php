<?php

namespace App\Models;

use App\Enums\RecordingStatus;
use Illuminate\Database\Eloquent\Model;

class Recording extends Model
{
    public function files()
    {
        return $this->hasMany(RecordingFile::class);
    }

    public function camera()
    {
        return $this->belongsTo(Camera::class);
    }

    public function isRecording()
    {
        return !$this->camera->isRecording() || $this->camera->recordings()->latest()->first()->id !== $this->id;
    }

    public function setStatus(RecordingStatus $status)
    {
        $this->update([
            'status' => $status,
        ]);
    }
}
