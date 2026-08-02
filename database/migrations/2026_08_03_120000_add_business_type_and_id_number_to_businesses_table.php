<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            // Defaulted, not nullable-plus-backfill: every existing row was
            // registered through the (until now) business-only form, so
            // 'business' is the correct value for pre-existing rows too —
            // and a column default applies to them automatically.
            $table->string('business_type')->default('business')->after('business_name');
            $table->string('id_number')->nullable()->after('registration_number');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['business_type', 'id_number']);
        });
    }
};
