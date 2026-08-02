<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_page_contents', function (Blueprint $table) {
            $table->string('contact_location')->nullable()->after('cta_banner_subtext');
            $table->string('contact_phone')->nullable()->after('contact_location');
        });
    }

    public function down(): void
    {
        Schema::table('landing_page_contents', function (Blueprint $table) {
            $table->dropColumn(['contact_location', 'contact_phone']);
        });
    }
};
