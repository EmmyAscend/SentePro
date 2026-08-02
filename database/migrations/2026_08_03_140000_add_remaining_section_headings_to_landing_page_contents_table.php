<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_page_contents', function (Blueprint $table) {
            $table->string('features_heading')->nullable()->after('features');
            $table->string('features_subtext')->nullable()->after('features_heading');
            $table->string('balances_heading')->nullable()->after('gateways_subtext');
            $table->string('balances_subtext')->nullable()->after('balances_heading');
            $table->string('payment_links_heading')->nullable()->after('balances_subtext');
            $table->string('payment_links_subtext')->nullable()->after('payment_links_heading');
        });

        // Backfill any row that existed before these columns were added with
        // the exact text those sections already hardcoded, so nothing
        // visually changes until a super admin actually edits it.
        DB::table('landing_page_contents')->whereNull('features_heading')->update([
            'features_heading' => 'Why SentePro?',
            'features_subtext' => 'Fast, flexible, and secure payment collection for growing businesses.',
            'balances_heading' => 'One dashboard for every balance',
            'balances_subtext' => 'See exactly where your money is — available to withdraw, reserved for settlement, or already paid out.',
            'payment_links_heading' => 'Share a link or QR code, get paid instantly',
            'payment_links_subtext' => 'Every payment link comes with a scannable QR code and a copyable checkout URL — no integration work required to start collecting.',
        ]);
    }

    public function down(): void
    {
        Schema::table('landing_page_contents', function (Blueprint $table) {
            $table->dropColumn([
                'features_heading',
                'features_subtext',
                'balances_heading',
                'balances_subtext',
                'payment_links_heading',
                'payment_links_subtext',
            ]);
        });
    }
};
