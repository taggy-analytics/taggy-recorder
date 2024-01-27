<?php

use App\Models\Camera;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('recordings', function (Blueprint $table) {
            $table->boolean('livestream_enabled')->after('key')->default(0);
        });
    }
};
