<?php

namespace App\Models;

use App\Enums\RecordingStatus;
use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Recording extends Model
{
    use HasStatus;

    protected $casts = [
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
        'status' => RecordingStatus::class,
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
        if(is_null($this->stopped_at) && !$this->camera->isRecording()) {
            $this->calculateStoppedAt();
        }

        return is_null($this->stopped_at);
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
        return $this->getEndTime()->diffInSeconds($this->started_at);
    }

    public function getUrl()
    {
        return Storage::disk('public')
            ->url($this->getPath('video/video.m3u8'));
    }

    public function getEndTime()
    {
        if($this->isRecording()) {
            return now();
        }
        else {
            if(!$this->stopped_at) {
                $this->calculateStoppedAt();
            }

            return $this->stopped_at;
        }
    }

    private function calculateStoppedAt()
    {
        $duration = exec('ffprobe ' . $this->getPath('video/video.m3u8') . ' -show_entries format=duration -v quiet -of csv="p=0"');
        $this->update([
            'stopped_at' => $this->started_at->addMilliseconds(round($duration * 1000)),
        ]);
    }
}
