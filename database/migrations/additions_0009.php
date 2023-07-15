<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mothership_reports', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->dateTime('reported_at')->nullable();
            $table->text('user_token');
            $table->timestamps();
        });
    }
};
