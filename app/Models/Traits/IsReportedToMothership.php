<?php

namespace App\Models\Traits;

use App\Models\MothershipReport;

trait IsReportedToMothership
{
    public static function bootIsReportedToMothership(): void
    {
        static::created(function ($model) {
            $model->createMothershipReport();
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

        if(!$this->mothershipReport) {
            info($this::class . '#' . $this->id . ' has no associated mothership report.');
            $this->createMothershipReport();
            $this->load('mothershipReport');
        }

        $this->mothershipReport->update($data);
    }

    public function createMothershipReport()
    {
        MothershipReport::updateOrCreate([
            'model_type' => $this::class,
            'model_id' => $this->id,
        ],[
            'user_token' => request()->header('User-Token'),
            'ready_to_send' => false,
        ]);
    }
}
