<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlement_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('status')->default('enabled');
            $table->unsignedInteger('processing_time');
            $table->string('time_unit');
            $table->decimal('settlement_fee_percent', 5, 2)->default(0);
            $table->decimal('settlement_fee_flat', 15, 2)->default(0);
            $table->decimal('platform_fee_percent', 5, 2)->default(0);
            $table->decimal('platform_fee_flat', 15, 2)->default(0);
            $table->decimal('min_amount', 15, 2)->nullable();
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->decimal('daily_limit', 15, 2)->nullable();
            $table->boolean('auto_approval')->default(false);
            $table->boolean('weekend_processing')->default(false);
            $table->text('public_description')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_methods');
    }
};
