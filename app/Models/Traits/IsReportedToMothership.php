<?php

namespace App\Models\Traits;

use App\Models\MothershipReport;
use Illuminate\Support\Str;

trait IsReportedToMothership
{
    public static function bootIsReportedToMothership(): void
    {
        static::created(function ($model) {
            MothershipReport::create([
                'model_type' => $model::class,
                'model_id' => $model->id,
                'user_token' => request()->header('User-Token', 'default'),
            ]);
        });
    }

    public function mothershipReport()
    {
        return $this->morphOne(MothershipReport::class, 'model');
    }
}
