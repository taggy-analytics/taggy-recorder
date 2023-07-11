<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->string('uuid')->index()->after('id');
            $table->string('container_uuid')->index()->after('uuid');
        });

        Schema::create('scene_containers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entity_id')->index();
            $table->string('uuid')->index();
            $table->string('name');
            $table->dateTime('start_time');
            $table->string('type');
            $table->string('sub_type');
            $table->timestamps();
        });
    }
};
