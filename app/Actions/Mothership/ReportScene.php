<?php

namespace App\Actions\Mothership;

use App\Models\Scene;
use App\Support\Mothership;

class ReportScene
{
    public function execute(Scene $scene)
    {
        Mothership::make()
            ->sendScene($scene);

        return true;
    }
}
