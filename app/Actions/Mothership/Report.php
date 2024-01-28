<?php

namespace App\Actions\Mothership;

use App\Support\Mothership;

abstract class Report
{
    protected Mothership $mothership;

    public function execute($model)
    {
        try {
            $this->mothership = Mothership::make($model->mothershipReport->userToken);
            if($this->executeReport($model)) {
                $model->mothershipReport->update(['processed_at' => now()]);
            }
            return true;
        }
        catch(\Exception $exception) {
            info($exception->getMessage());
            return false;
        }
    }
}
