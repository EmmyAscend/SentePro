<?php

use App\Models\LandingPageContent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_page_contents', function (Blueprint $table) {
            $table->json('heading_sizes')->nullable()->after('footer_tagline');
        });

        // Backfill any row that existed before this column was added with
        // every heading's own default tier, so nothing visually changes
        // until a super admin actually picks a different size.
        $defaults = json_encode(array_map(fn (array $heading) => $heading['default'], LandingPageContent::HEADING_KEYS));

        DB::table('landing_page_contents')->whereNull('heading_sizes')->update([
            'heading_sizes' => $defaults,
        ]);
    }

    public function down(): void
    {
        Schema::table('landing_page_contents', function (Blueprint $table) {
            $table->dropColumn('heading_sizes');
        });
    }
};
