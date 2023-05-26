<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('scenes', function (Blueprint $table) {
            $table->id();
            $table->dateTime('start_time', 3);
            $table->unsignedInteger('duration');
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }
};
