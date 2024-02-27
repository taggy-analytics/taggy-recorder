<?php

namespace App\Models;

use App\Actions\CalculateLed;
use App\CameraTypes\CameraType;
use App\Data\CredentialsStatusData;
use App\Enums\CameraStatus;
use App\Enums\RecordingMode;
use App\Models\Traits\BroadcastsEvents;
use App\Support\Mothership;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class Camera extends Model
{
    use BroadcastsEvents;

    public const DEFAULT_ROTATION = 1 / (60 * 4608);

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
            $camera->credentials = $camera->getType()->getDefaultCredentials();
            $camera->status = $camera->status ?? CameraStatus::DISCOVERED;
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
            $oldStatus = $this->status;
            $this->status = $this->getType()->getStatus($this);

            if($this->isDirty('status')) {
                $this->save();
                app(CalculateLed::class)->execute();
                info('Camera #' . $this->id . ' changed status: ' . $oldStatus->value . ' --> ' . $this->status->value);
            }
        }

        return $this->status;
    }

    public function isRecording()
    {
        return $this->getType()->isRecording($this);
    }

    public function startRecording($data)
    {
        $recording = $this->recordings()->create([
            'name' => now()->toDateTimeLocalString(),
            'data' => $data,
            'rotation' => $this->rotation,
            'livestream_enabled' => false, // Mothership::make()->isOnline(),
        ]);

        info('Starting recording # ' . $recording->id . ' for camera #' . $this->id);

        $this->getType()->startRecording($this, $recording);
        $recording->update(['started_at' => now()->subMilliseconds($this->getType()->getRecordingStartDelay())]);

        app(CalculateLed::class)->execute();

        return $recording;
    }

    public function getStreams()
    {
        return [
            [
                'type' => 'rtsp',
                'url' => $this->getType()->getRtspUrl($this), // ToDo: works only for RTSP cameras for now
            ]
        ];
    }

    public function stopRecording()
    {
        if($this->getType()->stopRecording($this)) {
            $recording = $this->recordings()->latest()->first();
            $recording->update(['stopped_at' => now()]);
            $recording->addM3u8EndTag();

            // The process file seems not to be deleted immediately
            cache()->put('recordingStoppedRecently', true, now()->addSeconds(5));

            app(CalculateLed::class)->execute();

            return $recording;
        }

        app(CalculateLed::class)->execute();

        return false;
    }

    public function getType() : CameraType
    {
        return (new $this->type);
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
