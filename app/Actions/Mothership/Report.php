<?php

namespace App\Actions\Mothership;

use App\Support\Mothership;

abstract class Report
{
    protected Mothership $mothership;

    public function __construct()
    {
        $this->mothership = Mothership::make();
    }
}
