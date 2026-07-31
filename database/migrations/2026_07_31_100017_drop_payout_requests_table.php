<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PayoutRequest duplicated Settlement (both were "withdraw from wallet"
     * requests); Settlement now has the full configuration engine and admin
     * processing workflow, so PayoutRequest is removed rather than kept in sync.
     */
    public function up(): void
    {
        Schema::dropIfExists('payout_requests');
    }

    public function down(): void
    {
        Schema::create('payout_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('method');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }
};
