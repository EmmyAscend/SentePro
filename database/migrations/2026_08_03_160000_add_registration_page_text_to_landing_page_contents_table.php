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
            $table->string('register_prompt_heading')->nullable()->after('payment_links_subtext');
            $table->string('register_individual_title')->nullable()->after('register_prompt_heading');
            $table->string('register_individual_description')->nullable()->after('register_individual_title');
            $table->string('register_business_title')->nullable()->after('register_individual_description');
            $table->string('register_business_description')->nullable()->after('register_business_title');
            $table->string('register_ngo_title')->nullable()->after('register_business_description');
            $table->string('register_ngo_description')->nullable()->after('register_ngo_title');
        });

        // Backfill any row that existed before these columns were added with
        // the exact text the registration page already hardcoded, so nothing
        // visually changes until a super admin actually edits it.
        DB::table('landing_page_contents')->whereNull('register_prompt_heading')->update([
            'register_prompt_heading' => 'What are you registering? Choose one below',
            'register_individual_title' => 'Individual',
            'register_individual_description' => 'Freelancers and sole proprietors collecting payments.',
            'register_business_title' => 'Business',
            'register_business_description' => 'Registered companies collecting payments for goods or services.',
            'register_ngo_title' => 'Non-Profit Organisation',
            'register_ngo_description' => 'NGOs collecting donations and program payments.',
        ]);
    }

    public function down(): void
    {
        Schema::table('landing_page_contents', function (Blueprint $table) {
            $table->dropColumn([
                'register_prompt_heading',
                'register_individual_title',
                'register_individual_description',
                'register_business_title',
                'register_business_description',
                'register_ngo_title',
                'register_ngo_description',
            ]);
        });
    }
};
