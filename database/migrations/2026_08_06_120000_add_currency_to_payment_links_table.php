<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Currency moves from a customer choice at checkout time to a business
     * admin choice at link-creation time. Existing links default to UGX,
     * matching the currency every seeded fee structure/settlement method
     * already assumes and the app's own dominant-currency bias throughout.
     */
    public function up(): void
    {
        Schema::table('payment_links', function (Blueprint $table) {
            $table->string('currency')->default('UGX')->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('payment_links', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
