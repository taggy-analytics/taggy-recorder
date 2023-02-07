<?php

use App\Enums\RecordingFileStatus;
use App\Enums\RecordingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('recordings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('status')->index()->default(RecordingStatus::default()->value);
            $table->unsignedInteger('camera_id')->index();
            $table->timestamps();
        });

        Schema::create('recording_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recording_id')->index();
            $table->string('name');
            $table->string('status')->index()->default(RecordingFileStatus::default()->value);
            $table->string('path');
            $table->timestamps();
        });
    }
};
