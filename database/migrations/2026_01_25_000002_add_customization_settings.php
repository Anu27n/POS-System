<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add customization fields to stores table
        Schema::table('stores', function (Blueprint $table) {
            $table->string('primary_color', 7)->nullable()->after('logo');
            $table->string('secondary_color', 7)->nullable()->after('primary_color');
            $table->string('accent_color', 7)->nullable()->after('secondary_color');
            $table->string('font_family')->nullable()->after('accent_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['primary_color', 'secondary_color', 'accent_color', 'font_family']);
        });
    }
};
