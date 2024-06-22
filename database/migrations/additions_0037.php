<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('recordings', function (Blueprint $table) {
            $table->dropColumn('process_id');
        });

        Schema::table('cameras', function (Blueprint $table) {
            $table->dropColumn('process_id');
        });
    }
};
