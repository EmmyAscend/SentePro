<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settlements', function (Blueprint $table) {
            $table->dropColumn('method');
        });

        Schema::table('settlements', function (Blueprint $table) {
            $table->foreignId('settlement_method_id')->nullable()->after('business_id')->constrained()->nullOnDelete();
            $table->decimal('gateway_fee', 15, 2)->default(0)->after('amount');
            $table->decimal('platform_fee', 15, 2)->default(0)->after('gateway_fee');
            $table->decimal('net_amount', 15, 2)->default(0)->after('platform_fee');
            $table->timestamp('estimated_completion_at')->nullable()->after('net_amount');
        });
    }

    public function down(): void
    {
        Schema::table('settlements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('settlement_method_id');
            $table->dropColumn(['gateway_fee', 'platform_fee', 'net_amount', 'estimated_completion_at']);
        });

        Schema::table('settlements', function (Blueprint $table) {
            $table->string('method')->after('business_id');
        });
    }
};
