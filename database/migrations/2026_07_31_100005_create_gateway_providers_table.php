<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gateway_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('provider');
            $table->string('status')->default('active');
            $table->string('environment')->default('sandbox');
            $table->string('webhook_url');
            $table->text('credentials');
            $table->string('supported_countries');
            $table->string('supported_currencies');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_providers');
    }
};
