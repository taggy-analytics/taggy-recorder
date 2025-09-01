<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cameras', function (Blueprint $table) {
            $table->float('rotation', 12, 9)->default(0)->after('recording_mode');
        });

        Schema::table('recordings', function (Blueprint $table) {
            $table->float('rotation', 12, 9)->nullable()->after('started_at');
        });
    }
};
