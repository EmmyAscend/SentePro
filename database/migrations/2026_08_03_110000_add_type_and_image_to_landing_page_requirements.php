<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Requirements gain a `type` (ties a section to a registration flow) and
     * `image_path` (per-section image) key — backfill every existing row's
     * items with a sensible type based on today's default titles (NGOs,
     * Businesses, Individuals) and a null image_path (falls back to the
     * hero image / illustration at render time), so nothing crashes on an
     * item shaped without these keys yet.
     */
    public function up(): void
    {
        $typeByTitle = [
            'NGOs' => 'ngo',
            'Businesses' => 'business',
            'Individuals' => 'individual',
        ];

        DB::table('landing_page_contents')->get()->each(function ($row) use ($typeByTitle) {
            $requirements = json_decode($row->requirements ?? '[]', true) ?: [];

            $requirements = collect($requirements)->map(function (array $item) use ($typeByTitle) {
                $item['type'] ??= $typeByTitle[$item['title']] ?? '';
                $item['image_path'] ??= null;

                return $item;
            })->all();

            DB::table('landing_page_contents')->where('id', $row->id)->update([
                'requirements' => json_encode($requirements),
            ]);
        });
    }

    public function down(): void
    {
        // Additive data within an existing JSON column — no schema to revert.
    }
};
