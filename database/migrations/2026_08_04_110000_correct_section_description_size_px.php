<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The previous migration doubled section_description_size_px (20->40)
     * per an explicit "double the descriptions" request, but paired against
     * the unchanged 48px section_heading_size_px that produced almost no
     * heading-vs-description contrast, and — combined with several sibling
     * titles that had no desktop size override at all — an actively broken
     * desktop layout (descriptions reading bigger than their own headings,
     * excessive wrapping). Corrects the stored value to a proportionate
     * 24px (a 50% heading:description ratio against the still-48px
     * heading), a flat set rather than another proportional scale since the
     * thing being corrected is the doubling itself, not a value worth
     * preserving a multiple of.
     */
    public function up(): void
    {
        DB::table('landing_page_contents')->update([
            'section_description_size_px' => 24,
        ]);
    }

    public function down(): void
    {
        DB::table('landing_page_contents')->update([
            'section_description_size_px' => 40,
        ]);
    }
};
