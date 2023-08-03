<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('recordings', function (Blueprint $table) {
            $table->dateTime('aborted_at')->nullable()->after('started_at');
            $table->unsignedBigInteger('restart_recording_id')->nullable()->after('aborted_at');
        });
    }
};
