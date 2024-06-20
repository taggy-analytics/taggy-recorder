<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('livestream_segments', function (Blueprint $table) {
            $table->text('m3u8_content')->nullable()->after('content');
        });
    }
};
