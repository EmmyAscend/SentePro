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
            $table->dropColumn(['stat_1_label', 'stat_1_value', 'stat_2_label', 'stat_2_value']);
            $table->json('requirements')->nullable()->after('payment_logos');
        });

        // Backfill any row that existed before this column was added — a plain
        // ->nullable() add leaves existing rows NULL, and the public page's
        // @foreach over requirements expects a real array, same reasoning
        // LandingPageContent::current()'s own defaults exist for a fresh row.
        DB::table('landing_page_contents')->whereNull('requirements')->update([
            'requirements' => json_encode([
                ['title' => 'NGOs', 'description' => "Registered non-profits and NGOs can collect donations and program payments — you'll need your registration certificate details and organization contact information to get verified."],
                ['title' => 'Businesses', 'description' => "Any registered business can start collecting payments — you'll need your business registration number, trading name, and expected monthly transaction volume."],
                ['title' => 'Individuals', 'description' => 'Freelancers and sole proprietors can collect payments too — a valid form of identification and your contact details are all you need to get started.'],
            ]),
        ]);
    }

    public function down(): void
    {
        Schema::table('landing_page_contents', function (Blueprint $table) {
            $table->string('stat_1_label')->nullable();
            $table->string('stat_1_value')->nullable();
            $table->string('stat_2_label')->nullable();
            $table->string('stat_2_value')->nullable();
            $table->dropColumn('requirements');
        });
    }
};
