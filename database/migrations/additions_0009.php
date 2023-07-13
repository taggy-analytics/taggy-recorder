<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->dateTime('reported_at')->nullable()->after('data');
        });

        Schema::create('reportables', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->dateTime('reported_at')->nullable();
            $table->string('user_token');
            $table->timestamps();
        });
    }
};
