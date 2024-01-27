<?php

use App\Models\Camera;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('livestream_segments', function (Blueprint $table) {
            $table->longText('content')->nullable()->after('last_failed_at');
        });
    }
};
