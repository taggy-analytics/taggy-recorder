<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('recorder_log', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index();
            $table->text('message')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }
};
