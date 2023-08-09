<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('model_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid()->index();
            $table->unsignedBigInteger('entity_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->uuidMorphs('model');
            $table->string('action');
            $table->string('property')->nullable()->index();
            $table->json('value')->nullable();
            $table->timestamp('created_at', 3)->useCurrent()->index();
            $table->string('error')->nullable();
        });
    }
};
