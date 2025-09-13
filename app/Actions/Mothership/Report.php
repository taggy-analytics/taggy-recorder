<?php

namespace App\Actions\Mothership;

use App\Enums\MothershipReportStatus;
use App\Support\Mothership;

abstract class Report
{
    protected Mothership $mothership;

    public function execute($model)
    {
        try {
            $this->mothership = Mothership::make($model->mothershipReport->userToken);
            if ($this->executeReport($model)) {
                $model->mothershipReport->update([
                    'processed_at' => now(),
                    'status' => MothershipReportStatus::Processed,
                ]);
            }

            return true;
        } catch (\Exception $exception) {
            info('Error while running mothership report #'.$model->mothershipReport->id.': '.$exception->getMessage());

            return false;
        }
    }
}
