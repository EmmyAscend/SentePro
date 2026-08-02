<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Icons are no longer shown on Requirements/Features items, so the
     * per-item `icon` key is dropped as dead data. Requirements are also
     * reordered to Individual, Business, then Non-Profit — matching
     * whatever order each item's `type` implies, with any item that isn't
     * one of the three standard types (or has none) kept in its original
     * relative order at the end.
     */
    public function up(): void
    {
        $priority = ['individual' => 0, 'business' => 1, 'ngo' => 2];

        DB::table('landing_page_contents')->get()->each(function ($row) use ($priority) {
            $requirements = json_decode($row->requirements ?? '[]', true) ?: [];
            $features = json_decode($row->features ?? '[]', true) ?: [];

            $requirements = collect($requirements)
                ->map(function (array $item) {
                    unset($item['icon']);

                    return $item;
                })
                ->sortBy(fn (array $item) => $priority[$item['type'] ?? ''] ?? 3)
                ->values()
                ->all();

            $features = collect($features)->map(function (array $item) {
                unset($item['icon']);

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
        // Icon removal and reordering aren't meaningfully reversible — the
        // original icon values and order are gone once dropped.
    }
};
