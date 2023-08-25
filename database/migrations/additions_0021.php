<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->uuid('parent_1')->index()->nullable()->after('user_id');
            $table->uuid('parent_2')->index()->nullable()->after('parent_1');
            $table->dropColumn('reported_to_mothership');
        });
    }
};
