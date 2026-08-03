<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A super admin can set an exact desktop font size (px) for the
     * heading and description of every full-bleed image/text section
     * (Hero, Requirements, How it works, Payment links spotlight) — a
     * more precise complement to the existing tier-based heading_sizes
     * clamp() system, which still governs mobile sizing for these same
     * headings. Defaulted (not nullable+backfilled): every existing row
     * gets a sensible, deliberately larger-than-today size automatically.
     */
    public function up(): void
    {
        Schema::table('landing_page_contents', function (Blueprint $table) {
            $table->unsignedSmallInteger('section_heading_size_px')->default(48)->after('heading_sizes');
            $table->unsignedSmallInteger('section_description_size_px')->default(20)->after('section_heading_size_px');
        });
    }

    public function down(): void
    {
        Schema::table('landing_page_contents', function (Blueprint $table) {
            $table->dropColumn(['section_heading_size_px', 'section_description_size_px']);
        });
    }
};
