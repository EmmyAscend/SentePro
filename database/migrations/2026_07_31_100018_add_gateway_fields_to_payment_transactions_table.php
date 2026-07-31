<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Real gateway integration needs: which PaymentLink this transaction belongs
     * to (checkout.status previously guessed "latest transaction for this
     * business", which breaks under concurrent checkouts), the gateway's own
     * transaction id (for status polling and webhook matching), and enough
     * customer contact info to satisfy each gateway's request requirements
     * (Pesapal needs email/phone on the billing address, Yo Payments needs a
     * phone number to push the payment prompt to) — none of which the original
     * checkout stub persisted even though the form already collected some of it.
     */
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->foreignId('payment_link_id')->nullable()->after('business_id')->constrained()->nullOnDelete();
            $table->string('provider_reference')->nullable()->after('external_reference');
            $table->string('customer_name')->nullable()->after('provider_reference');
            $table->string('customer_email')->nullable()->after('customer_name');
            $table->string('customer_phone')->nullable()->after('customer_email');
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_link_id');
            $table->dropColumn(['provider_reference', 'customer_name', 'customer_email', 'customer_phone']);
        });
    }
};
