<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * GatewayProvider moves from a per-business, tenant-scoped model (each
     * business connects its own Pesapal/Yo Payments merchant account) to a
     * platform-wide singleton-per-provider model (the super admin configures
     * SentePro's own single Pesapal account and single Yo Payments account,
     * used for every business). Existing rows are wiped rather than merged —
     * no real credentials were ever configured in this environment (every
     * gateway test so far is Http::fake()-based), and a per-business row has
     * no clean mapping onto a single platform-wide row anyway. gateway_logs
     * rows are wiped too, since they'd otherwise dangle against deleted
     * gateway_providers rows once business_id is no longer how they're keyed.
     */
    public function up(): void
    {
        DB::table('gateway_logs')->delete();
        DB::table('gateway_providers')->delete();

        Schema::table('gateway_logs', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropColumn('business_id');
        });

        Schema::table('gateway_providers', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropColumn(['business_id', 'name', 'supported_countries']);
            $table->unique('provider');
        });
    }

    public function down(): void
    {
        Schema::table('gateway_providers', function (Blueprint $table) {
            $table->dropUnique(['provider']);
            $table->foreignId('business_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('supported_countries')->nullable();
        });

        Schema::table('gateway_logs', function (Blueprint $table) {
            $table->foreignId('business_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }
};
