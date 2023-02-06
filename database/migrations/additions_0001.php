<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cameras', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index();
            $table->string('name');
            $table->string('identifier')->index();
            $table->string('status')->index();
            $table->string('ip_address')->nullable();
            $table->string('recording_mode')->nullable();
            $table->dateTime('sent_to_mothership_at')->nullable();
            $table->json('credentials')->nullable();
            $table->timestamps();
        });
    }
};
