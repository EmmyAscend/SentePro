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
            $table->string('hero_cta_text')->nullable()->after('hero_subtext');
            $table->string('requirements_heading')->nullable()->after('requirements');
            $table->string('requirements_subtext')->nullable()->after('requirements_heading');
            $table->string('how_it_works_heading')->nullable()->after('how_it_works_image_path');
            $table->json('how_it_works_steps')->nullable()->after('how_it_works_heading');
            $table->string('how_it_works_cta_text')->nullable()->after('how_it_works_steps');
            $table->string('faq_heading')->nullable()->after('faqs');
            $table->string('faq_subtext')->nullable()->after('faq_heading');
        });

        // Backfill any row that existed before these columns were added with
        // the exact text those sections already hardcoded, so nothing
        // visually changes until a super admin actually edits it.
        DB::table('landing_page_contents')->whereNull('hero_cta_text')->update([
            'hero_cta_text' => 'Start free onboarding',
            'requirements_heading' => 'Who can use SentePro?',
            'requirements_subtext' => "Whatever kind of organization you run, here's what you'll need to get verified.",
            'how_it_works_heading' => "It's simple to start using SentePro",
            'how_it_works_steps' => json_encode([
                ['title' => 'Register your business', 'description' => 'Submit your business and owner details in one form.'],
                ['title' => 'Get verified', 'description' => 'A super admin reviews and approves your business.'],
                ['title' => 'Connect a gateway', 'description' => 'Enable Pesapal for cards, Yo Payments for mobile money.'],
                ['title' => 'Collect & settle', 'description' => 'Share payment links and request settlements to your bank or wallet.'],
            ]),
            'how_it_works_cta_text' => 'Get started now',
            'faq_heading' => 'Frequently Asked Questions',
            'faq_subtext' => 'Find answers to frequently asked questions about SentePro.',
        ]);
    }

    public function down(): void
    {
        Schema::table('landing_page_contents', function (Blueprint $table) {
            $table->dropColumn([
                'hero_cta_text',
                'requirements_heading',
                'requirements_subtext',
                'how_it_works_heading',
                'how_it_works_steps',
                'how_it_works_cta_text',
                'faq_heading',
                'faq_subtext',
            ]);
        });
    }
};
