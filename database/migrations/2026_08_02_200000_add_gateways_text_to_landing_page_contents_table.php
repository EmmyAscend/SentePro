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
            $table->string('gateways_heading')->nullable()->after('cta_banner_subtext');
            $table->string('gateways_subtext')->nullable()->after('gateways_heading');
        });

        // Backfill any row that existed before this column was added with the
        // text that section already hardcoded, so nothing visually changes
        // until a super admin actually edits it.
        DB::table('landing_page_contents')->whereNull('gateways_heading')->update([
            'gateways_heading' => 'Supported payment ecosystem',
            'gateways_subtext' => 'Pesapal for cards, Yo Payments for mobile money.',
        ]);
    }

    public function down(): void
    {
        Schema::table('landing_page_contents', function (Blueprint $table) {
            $table->dropColumn(['gateways_heading', 'gateways_subtext']);
        });
    }
};
