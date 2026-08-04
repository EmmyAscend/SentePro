<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Doubles every existing row's already-configured
     * section_description_size_px (not just the column default) — a
     * super admin who already customized this away from 20 keeps that
     * same "doubled" relationship, rather than being silently reset to
     * a hardcoded 40.
     */
    public function up(): void
    {
        DB::table('landing_page_contents')->update([
            'section_description_size_px' => DB::raw('section_description_size_px * 2'),
        ]);
    }

    public function down(): void
    {
        DB::table('landing_page_contents')->update([
            'section_description_size_px' => DB::raw('section_description_size_px / 2'),
        ]);
    }
};
