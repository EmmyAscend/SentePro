<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_page_contents', function (Blueprint $table) {
            $table->id();
            $table->string('hero_badge_text');
            $table->string('hero_headline');
            $table->text('hero_subtext');
            $table->string('stat_1_label');
            $table->string('stat_1_value');
            $table->string('stat_2_label');
            $table->string('stat_2_value');
            $table->json('features');
            $table->json('faqs');
            $table->string('cta_banner_heading');
            $table->text('cta_banner_subtext');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_page_contents');
    }
};
