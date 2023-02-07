<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recording extends Model
{
    public function files()
    {
        return $this->hasMany(RecordingFile::class);
    }
}
