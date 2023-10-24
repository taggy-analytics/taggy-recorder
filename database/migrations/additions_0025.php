<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('user_token_id')->nullable()->after('processed_at');
            $table->dropColumn('user_token');
        });
    }
};
