<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * trading_name/registration_number/industry/expected_monthly_volume were
     * NOT NULL back when every registration was a "business" — now that
     * Individual/Non-Profit registrations skip some of these, required-ness
     * moves to per-type validation in StoreBusinessRequest instead of the
     * schema.
     */
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('trading_name')->nullable()->change();
            $table->string('registration_number')->nullable()->change();
            $table->string('industry')->nullable()->change();
            $table->string('expected_monthly_volume')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('trading_name')->nullable(false)->change();
            $table->string('registration_number')->nullable(false)->change();
            $table->string('industry')->nullable(false)->change();
            $table->string('expected_monthly_volume')->nullable(false)->change();
        });
    }
};
