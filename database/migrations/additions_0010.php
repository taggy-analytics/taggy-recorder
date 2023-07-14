<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('mothership_reports', function (Blueprint $table) {
            $table->boolean('ready_to_send')->default(0)->after('');
        });
    }
};
