<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cameras', function (Blueprint $table) {
            $table->unsignedInteger('video_width')->nullable()->after('recording_mode');
            $table->unsignedInteger('video_height')->nullable()->after('video_width');
        });
    }
};
