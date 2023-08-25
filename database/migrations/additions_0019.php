<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('entity_id')->index()->after('id');
            $table->dateTime('last_successfully_used_at')->nullable()->after('token');
        });
    }
};
