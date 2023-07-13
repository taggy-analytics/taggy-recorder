<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MothershipReport extends Model
{
    public function model()
    {
        return $this->morphTo();
    }
}
