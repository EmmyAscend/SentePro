<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_transaction_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('reference_number')->unique();
            $table->decimal('amount', 15, 2);
            $table->decimal('net_amount', 15, 2);
            $table->string('currency', 10);
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->timestamp('emailed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
