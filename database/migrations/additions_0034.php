<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropColumns('cameras', ['sent_to_mothership_at', 'credentials_status']);
    }
};
