<?php

namespace App\Models;

use App\CameraTypes\CameraType;
use App\Data\CredentialsStatusData;
use App\Enums\CameraStatus;
use App\Enums\RecordingMode;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Camera extends Model
{
    protected $casts = [
        'sent_to_mothership_at' => 'datetime',
        'status' => CameraStatus::class,
        'credentials' => AsCollection::class,
        'recording_mode' => RecordingMode::class,
        'credentials_status' => CredentialsStatusData::class,
    ];

    public static function boot() {
        parent::boot();

        static::creating(function(Camera $camera) {
            $camera->credentials_status = new CredentialsStatusData();
        });
    }

    public function recordings()
    {
        return $this->hasMany(Recording::class);
    }

    public function getHumanReadableTypeAttribute()
    {
        return $this->getType()->getName();
    }

    public function getStatus($refresh = true)
    {
        if($refresh) {
            $this->update([
                'status' => $this->getType()->getStatus($this),
            ]);
        }

        info('Camera #' . $this->id . ' status: ' . $this->status->value);

        return $this->status;
    }

    public function isRecording()
    {
        return $this->getType()->isRecording($this);
    }

    public function startRecording()
    {
        if(!File::exists($this->storagePath())) {
            File::makeDirectory($this->storagePath(), recursive: true);
        };

        $recording = $this->recordings()->create([
            'name' => now()->toDateTimeLocalString(),
            'started_at' => now(),
        ]);

        // ToDo: auf was muss started_at gesetzt werden? Experimentieren, wenn App läuft.

        info('Starting recording # ' . $recording->id . ' for camera #' . $this->id);

        $this->getType()->startRecording($this);

        return $recording;
    }

    public function stopRecording()
    {
        if($this->getType()->stopRecording($this)) {
            return $this->recordings()->latest()->first()->update(['stopped_at' => now()]);
        }

        return false;
    }

    public function getType() : CameraType
    {
        return (new $this->type);
    }

    /*
    public function getRecordings()
    {
        return collect(File::files($this->storagePath()))
            ->map(fn(SplFileInfo $file) => Recording::fromFile($file));
    }

    public function getRecording($name)
    {
        return $this->storagePath() . '/' . $name;
    }

    */
    public function storagePath()
    {
        return storage_path("app/cameras/{$this->id}/recordings");
    }

    public static function noCameraIsRecording()
    {
        foreach (Camera::all() as $camera) {
            if ($camera->isRecording()) {
                return false;
            }
        }
        return true;
    }
}
