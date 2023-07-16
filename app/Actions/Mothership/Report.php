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
            return $this->executeReport($model);
        }
        catch(\Exception $exception) {
            report($exception);
            return false;
        }
    }
}
