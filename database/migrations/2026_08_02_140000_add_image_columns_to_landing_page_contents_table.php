<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_page_contents', function (Blueprint $table) {
            $table->string('hero_image_path')->nullable()->after('cta_banner_subtext');
            $table->string('how_it_works_image_path')->nullable()->after('hero_image_path');
            $table->string('payment_links_image_path')->nullable()->after('how_it_works_image_path');
            $table->json('payment_logos')->nullable()->after('payment_links_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('landing_page_contents', function (Blueprint $table) {
            $table->dropColumn(['hero_image_path', 'how_it_works_image_path', 'payment_links_image_path', 'payment_logos']);
        });
    }
};
