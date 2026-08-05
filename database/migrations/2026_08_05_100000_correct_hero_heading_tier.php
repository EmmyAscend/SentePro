<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The Hero headline no longer has its own desktop-only px override —
     * it now renders through the same heading_sizes clamp() tier every
     * other section heading uses, matching "Who can use SentePro?" and
     * friends. Existing rows still carry the tier this key had before it
     * was briefly retired (xl), which reads far too large next to those
     * other sections. Corrects it to md, the same default every other
     * section heading already uses, so Hero matches them out of the box.
     */
    public function up(): void
    {
        DB::table('landing_page_contents')->get()->each(function ($row) {
            $sizes = json_decode($row->heading_sizes ?? '{}', true) ?: [];
            $sizes['hero'] = 'md';

            DB::table('landing_page_contents')
                ->where('id', $row->id)
                ->update(['heading_sizes' => json_encode($sizes)]);
        });
    }

    public function down(): void
    {
        DB::table('landing_page_contents')->get()->each(function ($row) {
            $sizes = json_decode($row->heading_sizes ?? '{}', true) ?: [];
            $sizes['hero'] = 'xl';

            DB::table('landing_page_contents')
                ->where('id', $row->id)
                ->update(['heading_sizes' => json_encode($sizes)]);
        });
    }
};
