<?php

namespace App\Models;

use App\Actions\CalculateLed;
use App\CameraTypes\CameraType;
use App\Enums\CameraStatus;
use App\Enums\RecordingMode;
use App\Models\Traits\BroadcastsEvents;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;

class Camera extends Model
{
    use BroadcastsEvents;

    protected $casts = [
        'status' => CameraStatus::class,
        'credentials' => AsCollection::class,
        'recording_mode' => RecordingMode::class,
    ];

    public static function boot() {
        parent::boot();

        static::creating(function(Camera $camera) {
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

            // Workaround for slow ffprobe on RTSP stream on Reolink
            // ToDo: works only for RTSP currently
            if($oldStatus == CameraStatus::READY && $this->isPortOpen(554)) {
                $newStatus = CameraStatus::READY;
            }
            else {
                $newStatus = $this->getType()->getStatus($this);
            }

            $this->status = $newStatus;

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

    public function startRecording($data = [])
    {
        $recording = $this->recordings()->create([
            'name' => now()->toDateTimeLocalString(),
            'data' => $data,
            'rotation' => $this->rotation,
            'livestream_enabled' => true, // Mothership::make()->isOnline(),
            'width' => $this->video_width,
            'height' => $this->video_height,
            'video_format' => $this->getType()->getVideoFormat(),
        ]);

        info('Starting recording # ' . $recording->id . ' for camera #' . $this->id);

        Process::start('php ' . base_path('artisan') . ' taggy:watch-recording-segments ' . $recording->id);

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
        info('Stopping recording...');

        if($this->getType()->stopRecording($this)) {
            $recording = $this->getLatestRecording();
            $recording->update(['stopped_at' => now()]);
            $recording->addM3u8EndTag();

            app(CalculateLed::class)->execute();

            return $recording;
        }

        app(CalculateLed::class)->execute();

        return false;
    }

    public function getLatestRecording()
    {
        return $this->recordings()->latest()->first();
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

    private function isPortOpen($port)
    {
        return Process::run("nc -z -v -w1 {$this->ip_address} {$port}")
            ->successful();
    }
}
