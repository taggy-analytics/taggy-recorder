<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $supervisorConfPath = '/etc/supervisor/conf.d/watchtest.conf';

        if (file_exists($supervisorConfPath)) {
            unlink($supervisorConfPath);

            exec('sudo supervisorctl reread && sudo supervisorctl update');
        }
    }
};
