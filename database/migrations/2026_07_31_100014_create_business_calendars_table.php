<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_calendars', function (Blueprint $table) {
            $table->id();
            $table->json('working_days');
            $table->time('business_hours_start');
            $table->time('business_hours_end');
            $table->time('cutoff_time');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_calendars');
    }
};
