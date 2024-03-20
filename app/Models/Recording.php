<?php

namespace App\Models;

use App\Enums\RecordingStatus;
use App\Models\Traits\BroadcastsEvents;
use App\Models\Traits\HasStatus;
use App\Models\Traits\HasUuid;
use App\Models\Traits\IsReportedToMothership;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Recording extends Model
{
    use HasStatus;
    use HasUuid;
    use IsReportedToMothership;
    use BroadcastsEvents;

    protected $dateFormat = 'Y-m-d H:i:s.v';

    protected $casts = [
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
        'aborted_at' => 'datetime',
        'status' => RecordingStatus::class,
        'data' => 'array',
        'livestream_enabled' => 'boolean',
    ];

    public static function boot() {
        parent::boot();

        static::deleting(function(Recording $recording) {
            $recording->files()->delete();
            Storage::disk('public')->deleteDirectory($recording->getPath());
        });

        static::creating(function(Recording $recording) {
            $recording->key = Str::random(32);
        });
    }

    public function scopeRunning(Builder $query)
    {
        return $query->where('status', RecordingStatus::CREATED);
    }

    public function scopeFreshlyAborted(Builder $query)
    {
        return $query
            ->whereNull('restart_recording_id')
            ->whereNotNull('aborted_at')
            ->where('aborted_at', '>', now()->subMinutes(config('taggy-recorder.recording.restart-aborted-recordings-timeout')));
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
        if(is_null($this->stopped_at) && !$this->camera?->isRecording()) {
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
        return $this->files->first()?->thumbnailPath();
    }

    public function getDuration()
    {
        return $this->getEndTime()->diffInMilliseconds($this->started_at) / 1000;
    }

    public function getUrl()
    {
        return Storage::disk('public')
            ->url($this->getM3u8Path());
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

    public function hasRestartedRecording()
    {
        return $this->hasOne(Recording::class, 'restart_recording_id');
    }

    public function cleanup()
    {
        $this->addM3u8EndTag();
        // ToDo: delete m4s files with filesize 0
    }

    public function getM3u8Path()
    {
        return $this->getPath('video/video.m3u8');
    }

    public function getInitMp4Path()
    {
        return $this->getPath('video/init.mp4');
    }

    public function addM3u8EndTag()
    {
        $m3u8 = Storage::disk('public')
            ->get($this->getM3u8Path());

        if(!Str::contains($m3u8, '#EXT-X-ENDLIST')) {
            $m3u8 .= PHP_EOL . '#EXT-X-ENDLIST';
            Storage::disk('public')
                ->put($this->getM3u8Path(), $m3u8);
        }
    }

    public function sceneFilename($startTime, $duration)
    {
        return md5(json_encode([$this->key, $startTime, $duration])) . '.mp4';
    }

    public function restart()
    {
        $newRecording = $this->camera->startRecording($this->data);

        /*
        $newRecording->update([
            'data' => [
                'assigned_container' => Arr::get($this->data, 'assigned_container'),
            ],
        ]);
        */

        $this->update(['restart_recording_id' => $newRecording->id]);
        return $newRecording;
    }

    private function calculateStoppedAt()
    {
        $duration = exec('ffprobe ' . $this->getM3u8Path() . ' -show_entries format=duration -v quiet -of csv="p=0"');

        if(!is_numeric($duration)) {
            if(!Storage::disk('public')->exists($this->getM3u8Path())) {
                $duration = 0;
            }
            else {
                $lastModified = Carbon::parse(Storage::disk('public')->lastModified($this->getM3u8Path()));
                $duration = $this->started_at->diffInSeconds($lastModified);
            }
        }

        $this->update([
            'stopped_at' => $this->started_at?->addMilliseconds(round($duration * 1000)),
        ]);
    }

    public function getStreamingProtocol()
    {
        return $this->camera->getType()->streamingProtocol;
    }

    public function getCodec()
    {
        return $this->camera->getType()->codec;
    }
}
