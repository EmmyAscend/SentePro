<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('staff_role')->nullable()->after('role');
            $table->json('permissions')->nullable()->after('business_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('staff')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('business_admin')->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['staff_role', 'permissions']);
        });
    }
};
