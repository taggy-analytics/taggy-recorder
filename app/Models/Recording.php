<?php

namespace App\Models;

use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Recording extends Model
{
    use HasStatus;

    protected $casts = [
        'stopped_at' => 'datetime',
    ];

    public static function boot() {
        parent::boot();

        static::deleting(function(Recording $recording) {
            $recording->files()->delete();
            Storage::disk('public')->deleteDirectory($recording->getPath());
        });

        static::creating(function(Recording $recording) {
            $recording->key = Str::random();
        });
    }

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

    public function getPath($path = '')
    {
        return 'recordings/' . $this->id . '/' . $this->key . '/' . $path;
    }

    public function thumbnailsPath()
    {
        return $this->getPath() . "thumbnails";
    }

    public function thumbnailsMoviePath()
    {
        return $this->thumbnailsPath() . '/thumbnails.mp4';
    }

    public function getThumbnail()
    {
        return $this->files->first()->thumbnailPath();
    }

    public function getDuration()
    {
        $endTime = $this->stopped_at ?? now();
        return $endTime->diffInSeconds($this->created_at);
    }
}
