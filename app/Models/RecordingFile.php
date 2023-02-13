<?php

namespace App\Models;

use App\Enums\RecordingFileStatus;
use App\Enums\RecordingFileType;
use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Model;

class RecordingFile extends Model
{
    use HasStatus;

    protected $casts = [
        'status' => RecordingFileStatus::class,
        'type' => RecordingFileType::class,
    ];

    public function recording()
    {
        return $this->belongsTo(Recording::class);
    }

    public function getPath($type)
    {
        return $this->recording->getPath() . '/' . $type . '/' . $this->name;
    }

}
