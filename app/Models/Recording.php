<?php

namespace App\Models;

use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Model;

class Recording extends Model
{
    use HasStatus;
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

    public function getPath()
    {
        return 'cameras/' . $this->camera->id . '/recordings/' . $this->id . '/';
    }

    public function thumbnailPath()
    {
        return "recordings/{$this->id}/thumbnails";
    }
}
