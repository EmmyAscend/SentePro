<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('enabled');
            $table->decimal('gateway_fee_percent', 5, 2)->default(0);
            $table->decimal('gateway_fee_flat', 15, 2)->default(0);
            $table->decimal('platform_fee_percent', 5, 2)->default(0);
            $table->decimal('platform_fee_flat', 15, 2)->default(0);
            $table->decimal('min_fee', 15, 2)->nullable();
            $table->decimal('max_fee', 15, 2)->nullable();
            $table->timestamps();

            $table->index(['provider', 'business_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};
