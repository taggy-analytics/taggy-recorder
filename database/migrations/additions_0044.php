<?php

use App\Enums\MothershipReportStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('mothership_reports', function (Blueprint $table) {
            $table->string('status')->default(MothershipReportStatus::Initialized->name)->after('model_id');
        });
    }
};
