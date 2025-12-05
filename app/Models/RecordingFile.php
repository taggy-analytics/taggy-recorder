<?php

namespace App\Models;

use App\Enums\RecordingFileStatus;
use App\Enums\RecordingFileType;
use App\Models\Traits\HasStatus;
use App\Models\Traits\InsertsChunked;
use App\Models\Traits\IsReportedToMothership;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RecordingFile extends Model
{
    use HasStatus;
    use InsertsChunked;
    use IsReportedToMothership;

    protected $casts = [
        'status' => RecordingFileStatus::class,
        'type' => RecordingFileType::class,
    ];

    public static function booted(): void
    {
        static::deleting(function (RecordingFile $model) {
            Storage::disk('public')->delete($model->videoPath());
        });
    }

    public function recording()
    {
        return $this->belongsTo(Recording::class);
    }

    private function getPath($type)
    {
        return $this->recording->getPath() . $type . '/' . $this->name;
    }

    public function thumbnailPath()
    {
        return Str::replace(['.ts', '.m4s'], '.jpg', $this->getPath('thumbnails'));
    }

    public function videoPath()
    {
        return $this->getPath('video');
    }
}
