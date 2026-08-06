<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The "Why SentePro?" section moves from a card grid to a divided-column
     * layout with a small category label above each headline (matching the
     * requested reference design), which needs a per-feature "eyebrow"
     * field that didn't exist before. Backfills existing rows' stored
     * features with the same eyebrow labels as the model's own default seed
     * data, keyed by matching title — any feature a super admin added or
     * renamed simply gets no eyebrow (rendered blank, not fabricated).
     */
    public function up(): void
    {
        $eyebrows = [
            'Unified payment collection' => 'Payments',
            'Verified business onboarding' => 'Onboarding',
            'Role-aware access' => 'Security',
            'Transparent settlement fees' => 'Settlements',
        ];

        DB::table('landing_page_contents')->get(['id', 'features'])->each(function ($row) use ($eyebrows) {
            $features = json_decode($row->features ?? '[]', true) ?: [];

            foreach ($features as &$feature) {
                $feature['eyebrow'] = $eyebrows[$feature['title'] ?? ''] ?? ($feature['eyebrow'] ?? '');
            }

            DB::table('landing_page_contents')
                ->where('id', $row->id)
                ->update(['features' => json_encode($features)]);
        });
    }

    public function down(): void
    {
        DB::table('landing_page_contents')->get(['id', 'features'])->each(function ($row) {
            $features = json_decode($row->features ?? '[]', true) ?: [];

            foreach ($features as &$feature) {
                unset($feature['eyebrow']);
            }

            DB::table('landing_page_contents')
                ->where('id', $row->id)
                ->update(['features' => json_encode($features)]);
        });
    }
};
