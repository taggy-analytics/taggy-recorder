<?php

namespace App\Models\Traits;

use App\Models\MothershipReport;

trait IsReportedToMothership
{
    public static function bootIsReportedToMothership(): void
    {
        static::created(function ($model) {
            MothershipReport::create([
                'model_type' => $model::class,
                'model_id' => $model->id,
                'user_token' => request()->header('User-Token'),
                'ready_to_send' => false,
            ]);
        });

        static::deleting(function ($model) {
            $model->mothershipReport->delete();
        });
    }

    public function mothershipReport()
    {
        return $this->morphOne(MothershipReport::class, 'model');
    }

    public function reportToMothership($userToken = null)
    {
        $data['ready_to_send'] = true;

        if($userToken) {
            $data['user_token'] = $userToken;
        }

        $this->mothershipReport->update($data);
    }
}
