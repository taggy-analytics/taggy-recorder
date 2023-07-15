<?php

namespace App\Actions\Mothership;

use App\Models\Scene;

class ReportScene extends Report
{
    public function executeReport(Scene $scene)
    {
        $this->mothership
            ->sendScene($scene);

        return true;
    }
}
