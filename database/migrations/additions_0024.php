<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_tokens', function (Blueprint $table) {
            $table->string('endpoint')->nullable()->after('user_id');
        });

        Schema::table('mothership_reports', function (Blueprint $table) {
            $table->string('endpoint')->nullable()->after('user_token');
        });
    }
};
