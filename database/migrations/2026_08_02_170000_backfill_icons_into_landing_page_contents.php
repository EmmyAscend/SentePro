<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Requirements/Features items didn't carry a stored icon before this
     * change — the public page picked one from a hardcoded array keyed by
     * loop position instead. Now that icon is a real per-item field, backfill
     * any existing row's items with the icon that same positional lookup
     * would have produced, so nothing visually changes until an admin
     * actually picks a different one.
     */
    public function up(): void
    {
        $requirementIcons = ['shield', 'banknotes', 'users'];
        $featureIcons = ['link', 'check', 'users', 'clipboard'];

        DB::table('landing_page_contents')->get()->each(function ($row) use ($requirementIcons, $featureIcons) {
            $requirements = json_decode($row->requirements ?? '[]', true) ?: [];
            $features = json_decode($row->features ?? '[]', true) ?: [];

            $requirements = collect($requirements)->map(function (array $item, int $i) use ($requirementIcons) {
                $item['icon'] ??= $requirementIcons[$i] ?? 'shield';

                return $item;
            })->all();

            $features = collect($features)->map(function (array $item, int $i) use ($featureIcons) {
                $item['icon'] ??= $featureIcons[$i] ?? 'link';

                return $item;
            })->all();

            DB::table('landing_page_contents')->where('id', $row->id)->update([
                'requirements' => json_encode($requirements),
                'features' => json_encode($features),
            ]);
        });
    }

    public function down(): void
    {
        // Icon keys are additive data within an existing JSON column — no
        // schema to revert, and stripping the key back out isn't meaningful.
    }
};
