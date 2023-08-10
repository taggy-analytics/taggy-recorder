<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    protected $casts = [
        'token' => 'encrypted',
    ];
}
