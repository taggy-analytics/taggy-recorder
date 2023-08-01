<?php

namespace App\Models;

use App\Enums\LogMessageType;
use Illuminate\Database\Eloquent\Model;

class RecorderLog extends Model
{
    protected $table = 'recorder_log';

    protected $casts = [
        'type' => LogMessageType::class,
        'data' => 'array',
        'reported_at' => 'datetime',
    ];
}
