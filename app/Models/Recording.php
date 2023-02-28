<?php

namespace App\Models;

use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Model;

class Recording extends Model
{
    use HasStatus;

    protected $casts = [
        'stopped_at' => 'datetime',
    ];

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

    public function thumbnailsPath()
    {
        return $this->getPath() . "thumbnails";
    }

    public function thumbnailsMoviePath()
    {
        return $this->thumbnailsPath() . '/thumbnails.mp4';
    }

    public function getDuration()
    {
        $endTime = $this->stopped_at ?? now();
        return $endTime->diffInSeconds($this->created_at);
    }
}
