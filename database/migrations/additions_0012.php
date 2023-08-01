<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('recorder_log', function (Blueprint $table) {
            $table->dateTime('reported_at')->nullable()->after('data');
        });
    }
};
