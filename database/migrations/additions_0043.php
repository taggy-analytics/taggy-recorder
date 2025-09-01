<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cameras', function (Blueprint $table) {
            $table->boolean('is_recording')->default(0)->after('recording_mode');
        });
    }
};
