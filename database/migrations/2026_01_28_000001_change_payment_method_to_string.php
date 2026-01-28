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
     * Change payment_method from ENUM to VARCHAR to support additional payment methods like razorpay.
     */
    public function up(): void
    {
        // Alter the ENUM to VARCHAR to support any payment method
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method VARCHAR(50) DEFAULT 'counter'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('online', 'counter', 'cash', 'card', 'razorpay') DEFAULT 'counter'");
    }
};
