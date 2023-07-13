<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MothershipReport extends Model
{
    protected $casts = [
        'user_token' => 'encrypted',
    ];

    public function model()
    {
        return $this->morphTo();
    }
}
