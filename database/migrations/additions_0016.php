<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('model_transactions', function (Blueprint $table) {
            $table->uuid('parent_uuid')->nullable()->after('user_id');
        });
    }
};
