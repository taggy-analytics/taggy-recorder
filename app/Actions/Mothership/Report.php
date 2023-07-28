<?php

namespace App\Actions\Mothership;

use App\Support\Mothership;

abstract class Report
{
    protected Mothership $mothership;

    public function execute($model)
    {
        try {
            $this->mothership = Mothership::make($model->mothershipReport->user_token);
            if($this->executeReport($model)) {
                $model->mothershipReport->update(['processed_at' => now()]);
            }
        }
        catch(\Exception $exception) {
            report($exception);
            return false;
        }
    }
}
