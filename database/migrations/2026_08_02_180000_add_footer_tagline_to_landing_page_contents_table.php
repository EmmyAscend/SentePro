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
            $table->string('footer_tagline')->nullable()->after('contact_phone');
        });

        // Backfill any row that existed before this column was added — a plain
        // ->nullable() add leaves existing rows NULL, and the footer would
        // otherwise render an empty line where the tagline used to be hardcoded.
        DB::table('landing_page_contents')->whereNull('footer_tagline')->update([
            'footer_tagline' => 'Payment collection infrastructure for East African businesses.',
        ]);
    }

    public function down(): void
    {
        Schema::table('landing_page_contents', function (Blueprint $table) {
            $table->dropColumn('footer_tagline');
        });
    }
};
