<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('recording_files', function (Blueprint $table) {
            $table->unsignedBigInteger('video_format_id')->nullable()->after('video_id');
        });
    }
};
