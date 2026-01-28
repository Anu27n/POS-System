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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('staff_id')->nullable()->after('role')->constrained('staff')->onDelete('set null');
            $table->foreignId('works_at_store_id')->nullable()->after('staff_id')->constrained('stores')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->dropForeign(['works_at_store_id']);
            $table->dropColumn(['staff_id', 'works_at_store_id']);
        });
    }
};
