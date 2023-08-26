<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_tokens', function (Blueprint $table) {
            $table->dateTime('last_rejected_at')->nullable()->after('last_successfully_used_at');
        });
    }
};
