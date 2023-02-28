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
            $table->string('recording_mode')->index()->default(\App\Enums\RecordingMode::default()->value);
            $table->dateTime('sent_to_mothership_at')->nullable();
            $table->json('credentials')->nullable();
            $table->timestamps();
        });

        Schema::create('recordings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('camera_id')->index();
            $table->string('name')->index();
            $table->string('status')->index()->default(\App\Enums\RecordingStatus::default()->value);
            $table->unsignedBigInteger('process_id')->nullable();
            $table->dateTime('stopped_at')->nullable();
            $table->timestamps();
        });

        Schema::create('recording_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recording_id')->index();
            $table->string('name');
            $table->string('type')->index();
            $table->unsignedBigInteger('video_id')->index()->nullable();
            $table->string('status')->index()->default(\App\Enums\RecordingFileStatus::default()->value);
            $table->timestamps();
        });

        Schema::create('cameras', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index();
            $table->string('name');
            $table->string('identifier')->index();
            $table->string('status')->index();
            $table->string('ip_address')->nullable();
            $table->string('recording_mode')->index()->default(\App\Enums\RecordingMode::default()->value);
            $table->unsignedBigInteger('process_id')->nullable();
            $table->dateTime('sent_to_mothership_at')->nullable();
            $table->json('credentials')->nullable();
            $table->timestamps();
        });
    }
};
