<?php

namespace App\Models;

use App\Enums\RecordingFileStatus;
use App\Enums\RecordingFileType;
use Illuminate\Database\Eloquent\Model;

class RecordingFile extends Model
{
    protected $casts = [
        'status' => RecordingFileStatus::class,
        'type' => RecordingFileType::class,
    ];
}
