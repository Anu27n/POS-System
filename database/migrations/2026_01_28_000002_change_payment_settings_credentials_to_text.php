<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Change credentials column from JSON to TEXT to support encrypted data.
     * The PaymentSetting model encrypts credentials before storing, which produces
     * a base64 string, not valid JSON. MySQL's JSON column type rejects this.
     */
    public function up(): void
    {
        // Change JSON column to TEXT for MySQL compatibility with encrypted data
        DB::statement("ALTER TABLE payment_settings MODIFY COLUMN credentials TEXT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: Reversing this could fail if there's encrypted data in the column
        DB::statement("ALTER TABLE payment_settings MODIFY COLUMN credentials JSON NULL");
    }
};
