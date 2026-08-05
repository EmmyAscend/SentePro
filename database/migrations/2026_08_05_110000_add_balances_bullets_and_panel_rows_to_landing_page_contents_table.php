<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The wallet-balances section's bullet list and balance-panel rows were
     * hardcoded directly in welcome.blade.php, unlike every other piece of
     * copy on that section, which meant a super admin had no way to edit
     * them. Backfills existing rows with that same hardcoded copy so the
     * section's content doesn't go blank now that it's admin-editable.
     */
    public function up(): void
    {
        Schema::table('landing_page_contents', function (Blueprint $table) {
            $table->json('balances_bullets')->nullable()->after('balances_subtext');
            $table->json('balances_panel_rows')->nullable()->after('balances_bullets');
        });

        DB::table('landing_page_contents')->update([
            'balances_bullets' => json_encode([
                'Request a settlement the moment funds are available',
                'Fees are calculated and locked in upfront',
                'Full transaction and settlement history, exportable to CSV',
            ]),
            'balances_panel_rows' => json_encode([
                ['label' => 'Available balance', 'value' => 'Ready to settle'],
                ['label' => 'Pending balance', 'value' => 'Awaiting settlement'],
                ['label' => 'Settlement balance', 'value' => 'Paid out'],
            ]),
        ]);
    }

    public function down(): void
    {
        Schema::table('landing_page_contents', function (Blueprint $table) {
            $table->dropColumn(['balances_bullets', 'balances_panel_rows']);
        });
    }
};
