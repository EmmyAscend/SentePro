<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gateway_providers', function (Blueprint $table) {
            $table->timestamp('last_checked_at')->nullable()->after('supported_currencies');
            $table->string('last_health_status')->nullable()->after('last_checked_at');
            $table->unsignedInteger('last_latency_ms')->nullable()->after('last_health_status');
            $table->text('last_error')->nullable()->after('last_latency_ms');
        });
    }

    public function down(): void
    {
        Schema::table('gateway_providers', function (Blueprint $table) {
            $table->dropColumn(['last_checked_at', 'last_health_status', 'last_latency_ms', 'last_error']);
        });
    }
};
