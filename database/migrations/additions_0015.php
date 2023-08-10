<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->text('token');
            $table->timestamps();
        });

        Schema::table('model_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_token_id')->nullable()->after('created_at');
        });
    }
};
